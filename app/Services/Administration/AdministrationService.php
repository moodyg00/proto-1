<?php

namespace App\Services\Administration;

use App\Models\ChangeLog;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Administration\AdministrationRepository;
use App\Support\BrandSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministrationService
{
    public function __construct(
        private readonly AdministrationRepository $repo
    ) {}

    // ── Dashboard ──────────────────────────────────────────────────────────

    public function getDashboardData(): array
    {
        return [
            'metrics'            => $this->repo->getDashboardMetrics(),
            'recent_change_log'  => $this->repo->getRecentChangeLog(20),
        ];
    }

    // ── Users ──────────────────────────────────────────────────────────────

    public function listUsers(array $filters = []): array
    {
        return [
            'users'   => $this->repo->paginateUsers($filters),
            'filters' => $filters,
        ];
    }

    public function showUser(string $id): array
    {
        $user = $this->repo->findUser($id);
        $recentActivity = ChangeLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        $tickets = \App\Models\Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->limit(10)
            ->get();
        $tasks = Task::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->limit(10)
            ->get();

        return compact('user', 'recentActivity', 'tickets', 'tasks');
    }

    public function createUser(array $data): User
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);

        $user = $this->repo->createUser($data);

        $this->logChange('users', $user->id, 'create', $data, 'User created');

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);

        $before = $user->only(['full_name', 'email', 'user_type', 'role', 'is_active']);
        $updated = $this->repo->updateUser($user, $data);

        $this->logChange('users', $user->id, 'update', [
            'before' => $before,
            'after'  => $updated->only(['full_name', 'email', 'user_type', 'role', 'is_active']),
        ], 'User updated');

        return $updated;
    }

    public function toggleUserActive(User $user): User
    {
        $newState = !$user->is_active;
        $updated  = $this->repo->updateUser($user, ['is_active' => $newState]);

        if (!$newState) {
            // Reassign open tasks/tickets to null (unassigned)
            Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->update(['assigned_to' => null]);

            \App\Models\Ticket::where('assigned_to', $user->id)
                ->where('status', 'open')
                ->update(['assigned_to' => null]);

            $this->logChange('users', $user->id, 'update', [
                'action' => 'deactivated',
                'open_tasks_unassigned'   => true,
                'open_tickets_unassigned' => true,
            ], 'User deactivated – tasks and tickets unassigned');
        } else {
            $this->logChange('users', $user->id, 'update', ['action' => 'activated'], 'User activated');
        }

        return $updated;
    }

    // ── Settings ───────────────────────────────────────────────────────────

    public function listSettings(array $filters = []): array
    {
        return [
            'settings' => $this->repo->allSettings($filters),
            'modules'  => Setting::distinct()->orderBy('module')->pluck('module'),
            'filters'  => $filters,
        ];
    }

    public function createSetting(array $data): Setting
    {
        $setting = $this->repo->createSetting($data);
        $this->logChange('settings', $setting->id, 'create', $data, 'Setting created');

        if (($setting->module === 'business') && ($setting->key === 'branding')) {
            BrandSettings::clearCache();
        }

        return $setting;
    }

    public function updateSetting(Setting $setting, array $data): Setting
    {
        $before  = $setting->only(['module', 'key', 'value']);
        $updated = $this->repo->updateSetting($setting, $data);
        $this->logChange('settings', $setting->id, 'update', [
            'before' => $before,
            'after'  => $updated->only(['module', 'key', 'value']),
        ], 'Setting updated');

        if (($updated->module === 'business') && ($updated->key === 'branding')) {
            BrandSettings::clearCache();
        }

        return $updated;
    }

    // ── Services ───────────────────────────────────────────────────────────

    public function listServices(array $filters = []): array
    {
        return [
            'services' => $this->repo->paginateServices($filters),
            'filters'  => $filters,
        ];
    }

    public function showService(string $id): array
    {
        return ['service' => $this->repo->findService($id)];
    }

    public function createService(array $data): Service
    {
        $service = $this->repo->createService($data);
        $this->logChange('services', $service->id, 'create', $data, 'Service created');
        return $service;
    }

    public function updateService(Service $service, array $data): Service
    {
        $before  = $service->only(['name', 'category', 'suggested_price', 'is_active']);
        $updated = $this->repo->updateService($service, $data);
        $this->logChange('services', $service->id, 'update', [
            'before' => $before,
            'after'  => $updated->only(['name', 'category', 'suggested_price', 'is_active']),
        ], 'Service updated');
        return $updated;
    }

    public function deleteService(Service $service): void
    {
        $this->logChange('services', $service->id, 'delete', ['name' => $service->name], 'Service deleted');
        $this->repo->deleteService($service);
    }

    // ── Products ───────────────────────────────────────────────────────────

    public function listProducts(array $filters = []): array
    {
        return [
            'products' => $this->repo->paginateProducts($filters),
            'filters'  => $filters,
        ];
    }

    public function showProduct(string $id): array
    {
        return ['product' => $this->repo->findProduct($id)];
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $inventoryData = $data['inventory'] ?? null;
            unset($data['inventory']);

            $product = $this->repo->createProduct($data);

            if ($inventoryData) {
                $this->repo->upsertInventory($product, $inventoryData);
            }

            $this->logChange('products', $product->id, 'create', $data, 'Product created');
            return $product;
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $inventoryData = $data['inventory'] ?? null;
            unset($data['inventory']);

            $before  = $product->only(['name', 'category', 'unit_price', 'is_for_sale']);
            $updated = $this->repo->updateProduct($product, $data);

            if ($inventoryData) {
                $this->repo->upsertInventory($product, $inventoryData);
            }

            $this->logChange('products', $product->id, 'update', [
                'before' => $before,
                'after'  => $updated->only(['name', 'category', 'unit_price', 'is_for_sale']),
            ], 'Product updated');

            return $updated;
        });
    }

    public function deleteProduct(Product $product): void
    {
        $this->logChange('products', $product->id, 'delete', ['name' => $product->name], 'Product deleted');
        $this->repo->deleteProduct($product);
    }

    // ── Inventory ──────────────────────────────────────────────────────────

    public function listInventory(array $filters = []): array
    {
        return [
            'inventory' => $this->repo->paginateInventory($filters),
            'filters'   => $filters,
        ];
    }

    public function adjustStock(Product $product, array $data): Inventory
    {
        $inv     = $this->repo->upsertInventory($product, $data);
        $isLow   = $inv->quantity_on_hand <= $inv->reorder_level;

        $this->logChange('inventory', $inv->id, 'update', $data, 'Stock adjusted');

        if ($isLow) {
            Task::create([
                'title'       => "Reorder stock: {$product->name}",
                'description' => "Quantity on hand ({$inv->quantity_on_hand}) has reached or fallen below reorder level ({$inv->reorder_level}).",
                'status'      => 'pending',
                'source'      => 'automation',
            ]);
            $this->logChange('inventory', $inv->id, 'update', [
                'alert' => 'low_stock',
                'product' => $product->name,
                'quantity_on_hand' => $inv->quantity_on_hand,
                'reorder_level' => $inv->reorder_level,
            ], 'Low-stock reorder task created automatically');
        }

        return $inv;
    }

    // ── Change Log ─────────────────────────────────────────────────────────

    public function listChangeLogs(array $filters = []): array
    {
        return [
            'change_logs' => $this->repo->paginateChangeLogs($filters),
            'filters'     => $filters,
        ];
    }

    public function showChangeLog(string $id): array
    {
        return ['change_log' => $this->repo->findChangeLog($id)];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function logChange(string $table, string $recordId, string $action, array $changes, string $note = ''): void
    {
        ChangeLog::create([
            'table_name' => $table,
            'record_id'  => $recordId,
            'action'     => $action,
            'user_id'    => auth()->id(),
            'changes'    => array_merge($changes, $note ? ['_note' => $note] : []),
        ]);
    }
}
