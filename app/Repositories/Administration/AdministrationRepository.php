<?php

namespace App\Repositories\Administration;

use App\Models\ChangeLog;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdministrationRepository
{
    // ── Dashboard metrics ──────────────────────────────────────────────────

    public function getDashboardMetrics(): array
    {
        $lowStockCount = Inventory::whereColumn('quantity_on_hand', '<=', 'reorder_level')->count();

        return [
            'total_active_users'       => User::where('is_active', true)->count(),
            'active_ai_agents'         => User::where('user_type', 'ai_agent')->where('is_active', true)->count(),
            'open_tickets'             => Ticket::where('status', 'open')->count(),
            'pending_tasks'            => Task::whereIn('status', ['pending', 'in_progress'])->count(),
            'recent_change_log_count'  => ChangeLog::where('created_at', '>=', now()->subDay())->count(),
            'low_stock_products'       => $lowStockCount,
            'active_services'          => Service::where('is_active', true)->count(),
        ];
    }

    public function getRecentChangeLog(int $limit = 20): Collection
    {
        return ChangeLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    // ── Users ──────────────────────────────────────────────────────────────

    public function paginateUsers(array $filters = []): LengthAwarePaginator
    {
        $q = User::query();
        if (!empty($filters['search'])) {
            $q->where(function ($sq) use ($filters) {
                $sq->where('full_name', 'ilike', "%{$filters['search']}%")
                   ->orWhere('email', 'ilike', "%{$filters['search']}%");
            });
        }
        if (!empty($filters['user_type'])) {
            $q->where('user_type', $filters['user_type']);
        }
        if (isset($filters['is_active'])) {
            $q->where('is_active', $filters['is_active']);
        }
        return $q->orderByDesc('created_at')->paginate(25)->withQueryString();
    }

    public function findUser(string $id): User
    {
        return User::findOrFail($id);
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    // ── Settings ───────────────────────────────────────────────────────────

    public function allSettings(array $filters = []): Collection
    {
        $q = Setting::query();
        if (!empty($filters['module'])) {
            $q->where('module', $filters['module']);
        }
        return $q->orderBy('module')->orderBy('key')->get();
    }

    public function findSetting(string $id): Setting
    {
        return Setting::findOrFail($id);
    }

    public function createSetting(array $data): Setting
    {
        return Setting::create($data);
    }

    public function updateSetting(Setting $setting, array $data): Setting
    {
        $setting->update($data);
        return $setting->fresh();
    }

    // ── Services ───────────────────────────────────────────────────────────

    public function paginateServices(array $filters = []): LengthAwarePaginator
    {
        $q = Service::query();
        if (!empty($filters['search'])) {
            $q->where('name', 'ilike', "%{$filters['search']}%");
        }
        if (!empty($filters['category'])) {
            $q->where('category', $filters['category']);
        }
        if (isset($filters['is_active'])) {
            $q->where('is_active', $filters['is_active']);
        }
        return $q->orderBy('name')->paginate(25)->withQueryString();
    }

    public function findService(string $id): Service
    {
        return Service::findOrFail($id);
    }

    public function createService(array $data): Service
    {
        return Service::create($data);
    }

    public function updateService(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    public function deleteService(Service $service): void
    {
        $service->delete();
    }

    // ── Products ───────────────────────────────────────────────────────────

    public function paginateProducts(array $filters = []): LengthAwarePaginator
    {
        $q = Product::with('inventory');
        if (!empty($filters['search'])) {
            $q->where('name', 'ilike', "%{$filters['search']}%");
        }
        if (!empty($filters['category'])) {
            $q->where('category', $filters['category']);
        }
        return $q->orderBy('name')->paginate(25)->withQueryString();
    }

    public function findProduct(string $id): Product
    {
        return Product::with('inventory')->findOrFail($id);
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    // ── Inventory ──────────────────────────────────────────────────────────

    public function paginateInventory(array $filters = []): LengthAwarePaginator
    {
        $q = Inventory::with('product');
        if (!empty($filters['search'])) {
            $q->whereHas('product', function ($sq) use ($filters) {
                $sq->where('name', 'ilike', "%{$filters['search']}%");
            });
        }
        if (!empty($filters['low_stock'])) {
            $q->whereColumn('quantity_on_hand', '<=', 'reorder_level');
        }
        return $q->orderByDesc('updated_at')->paginate(25)->withQueryString();
    }

    public function findInventory(string $id): Inventory
    {
        return Inventory::with('product')->findOrFail($id);
    }

    public function upsertInventory(Product $product, array $data): Inventory
    {
        $inv = Inventory::firstOrNew(['product_id' => $product->id]);
        $inv->fill($data)->save();
        return $inv->fresh(['product']);
    }

    // ── Change Log ─────────────────────────────────────────────────────────

    public function paginateChangeLogs(array $filters = []): LengthAwarePaginator
    {
        $q = ChangeLog::with('user');
        if (!empty($filters['table_name'])) {
            $q->where('table_name', $filters['table_name']);
        }
        if (!empty($filters['action'])) {
            $q->where('action', $filters['action']);
        }
        if (!empty($filters['user_id'])) {
            $q->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->whereDate('created_at', '<=', $filters['date_to']);
        }
        return $q->orderByDesc('created_at')->paginate(50)->withQueryString();
    }

    public function findChangeLog(string $id): ChangeLog
    {
        return ChangeLog::with('user')->findOrFail($id);
    }
}
