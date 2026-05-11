<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ComprehensiveDemoSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        $users = $this->seedUsers();
        $this->seedPasswordReset($users);
        $settings = $this->seedSettings($users);
        $company = $this->seedCompany($users);
        $organizations = $this->seedOrganizations($users);
        $contacts = $this->seedContacts($users, $organizations);
        $services = $this->seedServices($users);
        $products = $this->seedProducts($users);
        $inventory = $this->seedInventory($users, $products);
        $leads = $this->seedLeads($users, $contacts, $organizations);
        $opportunities = $this->seedOpportunities($users, $contacts, $organizations);
        $this->seedEstimates($users, $leads, $contacts, $organizations, $services, $products);
        $workOrders = $this->seedWorkOrders($users, $contacts, $services);
        $bookings = $this->seedBookings($users, $workOrders);
        $this->seedOperationsSupportTables($users, $organizations, $contacts, $products, $workOrders, $bookings, $services);
        $accounts = $this->fetchChartAccounts();
        $journals = $this->seedJournalEntries($users, $accounts);
        $accounting = $this->seedAccounting($users, $organizations, $contacts, $services, $products, $workOrders, $journals);
        $tickets = $this->seedTickets($users, $contacts, $organizations, $workOrders, $accounting['invoices']);
        $banking = $this->seedBanking($users, $organizations, $journals);
        $content = $this->seedContent($users, $workOrders, $products);
        $marketing = $this->seedMarketing($users, $content['images']);
        $integrations = $this->seedIntegrations($users);
        $tasks = $this->seedTasksAndAi($users, $leads, $opportunities, $contacts, $organizations, $tickets, $workOrders);
        $this->seedAdministration($users, $inventory, $products, $tasks, $settings, $company, $organizations, $contacts, $services, $workOrders, $accounting['invoices'], $integrations);
    }

    private function seedUsers(): array
    {
        if (! Schema::hasTable('users')) {
            return [];
        }

        $records = [
            [
                'email' => 'admin@admin.com',
                'username' => 'admin',
                'full_name' => 'Admin User',
                'password_hash' => Hash::make('admin'),
                'user_type' => 'human',
                'role' => 'admin',
                'is_active' => true,
                'description' => 'Primary administrator for seeded data.',
                'last_login_at' => $this->now,
            ],
            [
                'email' => 'ops.manager@applab.test',
                'username' => 'opsmanager',
                'full_name' => 'Olivia Manager',
                'password_hash' => Hash::make('password'),
                'user_type' => 'human',
                'role' => 'moderator',
                'is_active' => true,
                'description' => 'Operations manager for schedule and fulfillment workflows.',
                'last_login_at' => $this->now->copy()->subHours(2),
            ],
            [
                'email' => 'finance.lead@applab.test',
                'username' => 'financelead',
                'full_name' => 'Felix Ledger',
                'password_hash' => Hash::make('password'),
                'user_type' => 'human',
                'role' => 'moderator',
                'is_active' => true,
                'description' => 'Accounting lead for invoicing and reconciliation views.',
                'last_login_at' => $this->now->copy()->subDay(),
            ],
            [
                'email' => 'automation.agent@applab.test',
                'username' => 'automationagent',
                'full_name' => 'Automation Agent',
                'password_hash' => Hash::make('password'),
                'user_type' => 'ai_agent',
                'role' => 'user',
                'is_active' => true,
                'ai_model' => 'gpt-5.4',
                'description' => 'Autonomous assistant profile for AI module testing.',
                'last_login_at' => $this->now->copy()->subMinutes(30),
            ],
        ];

        $users = [];

        foreach ($records as $record) {
            $existing = DB::table('users')
                ->where('email', $record['email'])
                ->orWhere('username', $record['username'])
                ->first();

            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update($this->stamp($record));
                $users[$record['email']] = (array) DB::table('users')->where('id', $existing->id)->first();
                continue;
            }

            $users[$record['email']] = $this->ensureRecord('users', ['email' => $record['email']], $record);
        }

        return $users;
    }

    private function seedPasswordReset(array $users): void
    {
        if (! Schema::hasTable('password_resets') || $users === []) {
            return;
        }

        $admin = $users['admin@admin.com'] ?? reset($users);

        $this->ensureRecord('password_resets', ['user_id' => $admin['id']], [
            'user_id' => $admin['id'],
            'token_hash' => hash('sha256', 'seeded-reset-token'),
            'expires_at' => $this->now->copy()->addDay(),
            'used_at' => null,
        ]);
    }

    private function seedSettings(array $users): array
    {
        if (! Schema::hasTable('settings') || $users === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];

        $records = [
            ['module' => 'crm', 'key' => 'mail.from_address', 'value' => json_encode('hello@applab.test'), 'description' => 'Default CRM sender address.'],
            ['module' => 'crm', 'key' => 'mail.from_name', 'value' => json_encode('APP-LAB CRM'), 'description' => 'Default CRM sender name.'],
            ['module' => 'accounting', 'key' => 'default_currency', 'value' => json_encode('USD'), 'description' => 'Base accounting currency.'],
            ['module' => 'operations', 'key' => 'schedule_window_days', 'value' => json_encode(21), 'description' => 'Default planning horizon for schedule views.'],
        ];

        $settings = [];

        foreach ($records as $record) {
            $settings[$record['key']] = $this->ensureRecord('settings', [
                'module' => $record['module'],
                'key' => $record['key'],
            ], [
                ...$record,
                'is_sensitive' => false,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $settings;
    }

    private function seedCompany(array $users): array
    {
        if (! Schema::hasTable('companies') || $users === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];

        return $this->ensureRecord('companies', ['name' => 'APP-LAB Demo Co'], [
            'name' => 'APP-LAB Demo Co',
            'logo_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=400&q=80',
            'settings' => json_encode(['timezone' => 'America/Chicago', 'locale' => 'en']),
            'invoice_template' => json_encode(['accent' => '#0f766e', 'terms' => 'Net 15']),
            'address' => json_encode(['street' => '500 Commerce Ave', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78702']),
            'tax_settings' => json_encode(['filing_state' => 'TX', 'ein' => '12-3456789']),
            'is_active' => true,
            'created_by' => $admin['id'],
            'updated_by' => $admin['id'],
        ]);
    }

    private function seedOrganizations(array $users): array
    {
        if (! Schema::hasTable('organizations') || $users === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $records = [
            ['name' => 'Northstar Legal Group', 'relationship_type' => 'customer', 'organization_type' => 'company', 'industry' => 'Legal', 'phone' => '555-210-1001', 'status' => 'active', 'source' => 'referral', 'is_1099_vendor' => false],
            ['name' => 'Harborline Outfitters', 'relationship_type' => 'customer', 'organization_type' => 'company', 'industry' => 'Retail', 'phone' => '555-210-1002', 'status' => 'active', 'source' => 'facebook', 'is_1099_vendor' => false],
            ['name' => 'Blue Ferry Medical', 'relationship_type' => 'customer', 'organization_type' => 'company', 'industry' => 'Healthcare', 'phone' => '555-210-1003', 'status' => 'active', 'source' => 'website_organic', 'is_1099_vendor' => false],
            ['name' => 'Summit Field Services', 'relationship_type' => 'supplier', 'organization_type' => 'company', 'industry' => 'Trades', 'phone' => '555-210-2001', 'status' => 'active', 'source' => 'in_person', 'is_1099_vendor' => true],
            ['name' => 'Precision Supply House', 'relationship_type' => 'vendor', 'organization_type' => 'company', 'industry' => 'Distribution', 'phone' => '555-210-3001', 'status' => 'active', 'source' => 'referral', 'is_1099_vendor' => true],
            ['name' => 'Mercury Demo Banking', 'relationship_type' => 'partner', 'organization_type' => 'company', 'industry' => 'Finance', 'phone' => '555-210-4001', 'status' => 'active', 'source' => 'in_person', 'is_1099_vendor' => false],
        ];

        $organizations = [];

        foreach ($records as $record) {
            $organizations[$record['name']] = $this->ensureRecord('organizations', ['name' => $record['name']], [
                ...$record,
                'website' => 'https://example.com/'.Str::slug($record['name']),
                'address' => json_encode(['city' => 'Austin', 'state' => 'TX']),
                'tax_id' => 'TAX-'.substr(md5($record['name']), 0, 8),
                'notes' => json_encode(['seeded' => true]),
                'tags' => json_encode([Str::slug($record['relationship_type'])]),
                'last_contacted_at' => $this->now->copy()->subDays(rand(1, 20)),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $organizations;
    }

    private function seedContacts(array $users, array $organizations): array
    {
        if (! Schema::hasTable('contacts') || $users === [] || $organizations === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $records = [
            ['email' => 'morgan.diaz@example.com', 'name' => 'Morgan Diaz', 'type' => 'customer', 'organization' => 'Northstar Legal Group', 'phone' => '555-120-1001'],
            ['email' => 'talia.brooks@example.com', 'name' => 'Talia Brooks', 'type' => 'customer', 'organization' => 'Harborline Outfitters', 'phone' => '555-120-1002'],
            ['email' => 'avery.patel@example.com', 'name' => 'Avery Patel', 'type' => 'customer', 'organization' => 'Blue Ferry Medical', 'phone' => '555-120-1003'],
            ['email' => 'sample.contractor@applab.test', 'name' => 'Sample Contractor', 'type' => 'contractor', 'organization' => 'Summit Field Services', 'phone' => '555-0199'],
            ['email' => 'field.tech.two@applab.test', 'name' => 'Jordan Reese', 'type' => 'contractor', 'organization' => 'Summit Field Services', 'phone' => '555-0188'],
            ['email' => 'ap@precisionsupply.test', 'name' => 'Casey Quinn', 'type' => 'vendor', 'organization' => 'Precision Supply House', 'phone' => '555-310-1001'],
        ];

        $contacts = [];

        foreach ($records as $record) {
            $organization = $organizations[$record['organization']];
            $contacts[$record['email']] = $this->ensureRecord('contacts', ['email' => $record['email']], [
                'organization_id' => $organization['id'],
                'title' => 'Primary Contact',
                'type' => $record['type'],
                'name' => $record['name'],
                'phone' => $record['phone'],
                'email' => $record['email'],
                'address' => json_encode(['city' => 'Austin', 'state' => 'TX']),
                'source' => 'seeded_demo',
                'last_contacted_at' => $this->now->copy()->subDays(rand(1, 10)),
                'status' => 'active',
                'notes' => json_encode(['seeded' => true]),
                'tags' => json_encode([$record['type']]),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $contacts;
    }

    private function seedServices(array $users): array
    {
        if (! Schema::hasTable('services') || $users === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $records = [
            ['name' => 'Exterior House Wash', 'category' => 'cleaning', 'estimated_duration_minutes' => 240, 'suggested_price' => 950],
            ['name' => 'Driveway Cleaning', 'category' => 'cleaning', 'estimated_duration_minutes' => 180, 'suggested_price' => 450],
            ['name' => 'Roof Treatment', 'category' => 'cleaning', 'estimated_duration_minutes' => 210, 'suggested_price' => 780],
            ['name' => 'HVAC Preventive Visit', 'category' => 'hvac', 'estimated_duration_minutes' => 120, 'suggested_price' => 325],
        ];

        $services = [];

        foreach ($records as $record) {
            $services[$record['name']] = $this->ensureRecord('services', ['name' => $record['name']], [
                ...$record,
                'description' => $record['name'].' service package for seeded demo flows.',
                'quote_prompt' => 'Create a fast quote for '.$record['name'].'.',
                'web_page_url' => 'https://example.com/services/'.Str::slug($record['name']),
                'web_content_summary' => 'Demo summary for '.$record['name'],
                'best_headline' => $record['name'].' booked faster',
                'best_hook' => 'Reliable crews and clear pricing.',
                'best_cta' => 'Book now',
                'q_and_a' => json_encode([['q' => 'Turnaround?', 'a' => 'Within 48 hours.']]),
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $services;
    }

    private function seedProducts(array $users): array
    {
        if (! Schema::hasTable('products') || $users === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $records = [
            ['sku' => 'CHEM-SH-001', 'name' => 'Soft Wash Detergent', 'category' => 'materials', 'unit_price' => 28.50, 'unit_of_measure' => 'gallon'],
            ['sku' => 'MAT-HOSE-001', 'name' => 'Pressure Hose', 'category' => 'equipment', 'unit_price' => 145.00, 'unit_of_measure' => 'each'],
            ['sku' => 'SAFE-GLOVE-001', 'name' => 'Chemical Gloves', 'category' => 'safety_gear', 'unit_price' => 12.00, 'unit_of_measure' => 'pair'],
            ['sku' => 'CONS-NOZZLE-001', 'name' => 'Fan Spray Nozzle', 'category' => 'consumables', 'unit_price' => 18.00, 'unit_of_measure' => 'each'],
        ];

        $products = [];

        foreach ($records as $record) {
            $products[$record['sku']] = $this->ensureRecord('products', ['sku' => $record['sku']], [
                ...$record,
                'description' => 'Seeded product record for '.$record['name'],
                'is_for_sale' => false,
                'is_internal_use' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $products;
    }

    private function seedInventory(array $users, array $products): array
    {
        if (! Schema::hasTable('inventory') || $users === [] || $products === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $inventory = [];
        $levels = [
            'CHEM-SH-001' => [32, 4, 12, 24, 'Warehouse A'],
            'MAT-HOSE-001' => [6, 1, 3, 2, 'Truck Bay'],
            'SAFE-GLOVE-001' => [14, 2, 20, 40, 'Safety Locker'],
            'CONS-NOZZLE-001' => [2, 0, 5, 10, 'Warehouse A'],
        ];

        foreach ($levels as $sku => [$onHand, $reserved, $reorder, $reorderQty, $location]) {
            $product = $products[$sku];
            $inventory[$sku] = $this->ensureRecord('inventory', ['product_id' => $product['id']], [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'quantity_on_hand' => $onHand,
                'quantity_reserved' => $reserved,
                'reorder_level' => $reorder,
                'reorder_quantity' => $reorderQty,
                'location' => $location,
                'last_purchased_at' => $this->now->copy()->subDays(14),
                'last_used_at' => $this->now->copy()->subDays(2),
                'notes' => 'Seeded inventory state',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $inventory;
    }

    private function seedLeads(array $users, array $contacts, array $organizations): array
    {
        if (! Schema::hasTable('leads') || $users === [] || $contacts === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $records = [
            ['email' => 'morgan.diaz@example.com', 'status' => 'contacted', 'source' => 'referral', 'expected_value' => 4200, 'title' => 'Quarterly office deep clean'],
            ['email' => 'talia.brooks@example.com', 'status' => 'quoted', 'source' => 'facebook', 'expected_value' => 6800, 'title' => 'Retail storefront maintenance'],
            ['email' => 'avery.patel@example.com', 'status' => 'booked', 'source' => 'website_organic', 'expected_value' => 12500, 'title' => 'Multi-site janitorial agreement'],
        ];

        $leads = [];

        foreach ($records as $index => $record) {
            $contact = $contacts[$record['email']];
            $organizationId = $contact['organization_id'];
            $org = collect($organizations)->firstWhere('id', $organizationId);
            $leads[$record['email']] = $this->ensureRecord('leads', ['email' => $record['email']], [
                'contact_id' => $contact['id'],
                'organization_id' => $organizationId,
                'title' => $record['title'],
                'name' => $contact['name'],
                'phone' => $contact['phone'],
                'email' => $record['email'],
                'source' => $record['source'],
                'status' => $record['status'],
                'assigned_to' => $admin['id'],
                'next_follow_up' => $this->now->copy()->addDays($index + 2),
                'last_contacted_at' => $this->now->copy()->subDays($index + 1),
                'notes' => json_encode(['origin' => 'comprehensive-demo']),
                'expected_value' => $record['expected_value'],
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $leads;
    }

    private function seedOpportunities(array $users, array $contacts, array $organizations): array
    {
        if (! Schema::hasTable('opportunities') || $users === [] || $contacts === []) {
            return [];
        }

        $opsUser = $users['ops.manager@applab.test'] ?? reset($users);
        $records = [
            ['title' => 'Northstar Expansion Janitorial', 'contact_email' => 'morgan.diaz@example.com', 'status' => 'researching', 'priority' => 'medium', 'value' => 9800],
            ['title' => 'Harborline Seasonal Promo Cleanup', 'contact_email' => 'talia.brooks@example.com', 'status' => 'in_progress', 'priority' => 'high', 'value' => 7500],
        ];

        $opportunities = [];

        foreach ($records as $record) {
            $contact = $contacts[$record['contact_email']];
            $organizationId = $contact['organization_id'];
            $org = collect($organizations)->firstWhere('id', $organizationId);
            $opportunities[$record['title']] = $this->ensureRecord('opportunities', ['title' => $record['title']], [
                'title' => $record['title'],
                'description' => 'Seeded opportunity record to exercise CRM board and table states.',
                'contact_id' => $contact['id'],
                'contact_name' => $contact['name'],
                'organization_id' => $organizationId,
                'organization_name' => $org['name'] ?? null,
                'estimated_value' => $record['value'],
                'status' => $record['status'],
                'priority' => $record['priority'],
                'target_date' => $this->now->copy()->addDays(30)->toDateString(),
                'assigned_to' => $opsUser['id'],
                'notes' => json_encode(['seeded' => true]),
                'created_by' => $opsUser['id'],
                'updated_by' => $opsUser['id'],
            ]);
        }

        return $opportunities;
    }

    private function seedEstimates(array $users, array $leads, array $contacts, array $organizations, array $services, array $products): void
    {
        if (! Schema::hasTable('estimates') || $users === [] || $contacts === []) {
            return;
        }

        $opsUser = $users['ops.manager@applab.test'] ?? reset($users);
        $lead = $leads['morgan.diaz@example.com'] ?? reset($leads);
        $contact = $contacts['morgan.diaz@example.com'] ?? reset($contacts);
        $organization = collect($organizations)->firstWhere('id', $contact['organization_id']);
        $service = $services['Exterior House Wash'] ?? reset($services);
        $product = $products['CHEM-SH-001'] ?? reset($products);

        $this->ensureRecord('estimates', ['estimate_number' => 'EST-DEMO-2001'], [
            'estimate_number' => 'EST-DEMO-2001',
            'version_number' => 1,
            'parent_estimate_id' => null,
            'lead_id' => $lead['id'] ?? null,
            'contact_id' => $contact['id'],
            'contact_name' => $contact['name'],
            'organization_id' => $organization['id'] ?? null,
            'organization_name' => $organization['name'] ?? null,
            'title' => 'Northstar exterior refresh proposal',
            'description' => 'Seeded estimate for CRM and operations pricing views.',
            'line_items' => json_encode([
                ['label' => $service['name'] ?? 'Service', 'qty' => 1, 'unit_price' => 1600],
                ['label' => $product['name'] ?? 'Materials', 'qty' => 1, 'unit_price' => 125],
            ]),
            'subtotal' => 1725,
            'discount_amount' => 75,
            'tax_amount' => 125,
            'total_amount' => 1775,
            'payment_terms' => '50% deposit, balance due at completion',
            'status' => 'sent',
            'valid_until' => $this->now->copy()->addDays(14),
            'notes' => json_encode(['seeded' => true]),
            'metadata' => json_encode(['template' => 'commercial-cleaning']),
            'sent_at' => $this->now->copy()->subDays(2),
            'accepted_at' => null,
            'created_by' => $opsUser['id'],
            'updated_by' => $opsUser['id'],
        ]);
    }

    private function seedWorkOrders(array $users, array $contacts, array $services): array
    {
        if (! Schema::hasTable('work_orders') || $users === [] || $contacts === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $contractor = $contacts['sample.contractor@applab.test'];
        $customerA = $contacts['morgan.diaz@example.com'];
        $customerB = $contacts['talia.brooks@example.com'];
        $customerC = $contacts['avery.patel@example.com'];

        $records = [
            ['number' => 'WO-DEMO-1001', 'contact' => $customerA, 'service' => 'Exterior House Wash', 'status' => 'scheduled', 'contractor_status' => 'accepted', 'days' => 1, 'time' => '09:00:00'],
            ['number' => 'WO-DEMO-1002', 'contact' => $customerB, 'service' => 'Driveway Cleaning', 'status' => 'scheduled', 'contractor_status' => 'pending', 'days' => 3, 'time' => '13:30:00'],
            ['number' => 'WO-DEMO-1003', 'contact' => $customerC, 'service' => 'Roof Treatment', 'status' => 'completed', 'contractor_status' => 'accepted', 'days' => -2, 'time' => '10:15:00'],
            ['number' => 'WO-DEMO-1004', 'contact' => $customerA, 'service' => 'HVAC Preventive Visit', 'status' => 'rework', 'contractor_status' => 'accepted', 'days' => 5, 'time' => '11:00:00'],
        ];

        $workOrders = [];

        foreach ($records as $record) {
            $service = $services[$record['service']] ?? null;
            $scheduledDate = $this->now->copy()->addDays($record['days'])->toDateString();
            $workOrders[$record['number']] = $this->ensureRecord('work_orders', ['work_order_number' => $record['number']], [
                'work_order_number' => $record['number'],
                'invoice_number' => str_replace('WO', 'INV', $record['number']),
                'contact_id' => $record['contact']['id'],
                'customer_name' => $record['contact']['name'],
                'service_id' => $service['id'] ?? null,
                'service_name' => $service['name'] ?? $record['service'],
                'assigned_contractor_id' => $contractor['id'],
                'assigned_contractor' => $contractor['name'],
                'contractor_status' => $record['contractor_status'],
                'status' => $record['status'],
                'scheduled_date' => $scheduledDate,
                'booking_date' => $scheduledDate,
                'booking_time' => $record['time'],
                'address' => json_encode(['street' => rand(100, 999).' Demo Street', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78701']),
                'special_instructions' => 'Seeded work order instructions for QA and workflow review.',
                'notes' => json_encode(['source' => 'comprehensive-demo']),
                'completed_at' => $record['status'] === 'completed' ? $this->now->copy()->subDays(2) : null,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $workOrders;
    }

    private function seedBookings(array $users, array $workOrders): array
    {
        if (! Schema::hasTable('bookings') || $users === [] || $workOrders === []) {
            return [];
        }

        $admin = $users['admin@admin.com'];
        $bookings = [];

        foreach ($workOrders as $number => $workOrder) {
            $bookings[$number] = $this->ensureRecord('bookings', ['work_order_id' => $workOrder['id']], [
                'work_order_id' => $workOrder['id'],
                'booking_date' => $workOrder['booking_date'],
                'start_time' => $workOrder['booking_time'],
                'end_time' => Carbon::parse($workOrder['booking_time'])->addHours(2)->format('H:i:s'),
                'address' => $workOrder['address'],
                'duration_minutes' => 120,
                'notes' => 'Seeded booking synced from work order.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $bookings;
    }

    private function seedOperationsSupportTables(array $users, array $organizations, array $contacts, array $products, array $workOrders, array $bookings, array $services): void
    {
        $admin = $users['admin@admin.com'] ?? null;
        if (! $admin) {
            return;
        }

        $contractor = $contacts['sample.contractor@applab.test'] ?? null;
        $vendor = $organizations['Precision Supply House'] ?? null;
        $product = $products['CHEM-SH-001'] ?? reset($products);
        $secondaryProduct = $products['SAFE-GLOVE-001'] ?? $product;
        $firstWorkOrder = $workOrders['WO-DEMO-1001'] ?? reset($workOrders);
        $completedWorkOrder = $workOrders['WO-DEMO-1003'] ?? $firstWorkOrder;

        if (Schema::hasTable('work_order_materials')) {
            $this->ensureRecord('work_order_materials', ['work_order_id' => $firstWorkOrder['id'], 'product_name' => $product['name']], [
                'work_order_id' => $firstWorkOrder['id'],
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'quantity' => 3,
                'unit_cost' => 28.50,
                'total_cost' => 85.50,
                'source' => 'inventory',
                'is_billable' => true,
                'notes' => 'Seeded material usage.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('work_order_status_history')) {
            $this->insertIfEmpty('work_order_status_history', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $completedWorkOrder['id'],
                'old_status' => 'scheduled',
                'new_status' => 'completed',
                'changed_by' => $admin['id'],
                'notes' => 'Completed by seeded demo flow.',
                'changed_at' => $this->now->copy()->subDays(2),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('work_order_photos')) {
            $this->ensureRecord('work_order_photos', ['work_order_id' => $completedWorkOrder['id'], 'photo_url' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80'], [
                'work_order_id' => $completedWorkOrder['id'],
                'photo_url' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80',
                'description' => 'After photo for seeded job.',
                'uploaded_by' => $admin['id'],
                'uploaded_at' => $this->now->copy()->subDay(),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('work_order_documents')) {
            $this->insertIfEmpty('work_order_documents', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $firstWorkOrder['id'],
                'document_name' => 'Scope of Work.pdf',
                'document_url' => 'https://example.com/docs/scope-of-work.pdf',
                'document_type' => 'scope',
                'description' => 'Seeded scope PDF.',
                'uploaded_by' => $admin['id'],
                'uploaded_at' => $this->now->copy()->subDays(3),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('work_order_time_logs')) {
            $this->insertIfEmpty('work_order_time_logs', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $completedWorkOrder['id'],
                'started_at' => $this->now->copy()->subDays(2)->setTime(9, 0),
                'ended_at' => $this->now->copy()->subDays(2)->setTime(11, 15),
                'duration_minutes' => 135,
                'activity_type' => 'service',
                'notes' => 'Seeded completion time log.',
                'logged_by' => $admin['id'],
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('contractor_performance') && $contractor) {
            $this->insertIfEmpty('contractor_performance', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $completedWorkOrder['id'],
                'contractor_id' => $contractor['id'],
                'rating' => 5,
                'quality' => 5,
                'timeliness' => 4,
                'communication' => 5,
                'notes' => 'Strong performance on seeded work order.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('safety_incidents')) {
            $this->insertIfEmpty('safety_incidents', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $firstWorkOrder['id'],
                'incident_date' => $this->now->copy()->subDay()->toDateString(),
                'description' => 'Minor slip hazard reported and mitigated on site.',
                'severity' => 'low',
                'reported_by' => $admin['id'],
                'actions_taken' => 'Added traction mats and revised setup checklist.',
                'follow_up_required' => false,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('reviews')) {
            $this->insertIfEmpty('reviews', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $completedWorkOrder['id'],
                'contact_id' => $completedWorkOrder['contact_id'],
                'platform' => 'Google',
                'review_text' => 'Crew showed up on time and left the site spotless.',
                'sentiment_score' => 4.8,
                'responded_to' => true,
                'review_date' => $this->now->copy()->subDay()->toDateString(),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('quality_reviews')) {
            $review = DB::table('reviews')->first();
            $performance = Schema::hasTable('contractor_performance') ? DB::table('contractor_performance')->first() : null;
            $incident = Schema::hasTable('safety_incidents') ? DB::table('safety_incidents')->first() : null;

            $this->insertIfEmpty('quality_reviews', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $completedWorkOrder['id'],
                'before_photos' => json_encode(['https://example.com/photos/before-1.jpg']),
                'after_photos' => json_encode(['https://example.com/photos/after-1.jpg']),
                'issues_encountered' => 'Light oxidation required a second pass.',
                'safety_issues' => 'No open issues after closeout.',
                'customer_signoff_notes' => 'Customer approved work at closeout.',
                'work_order_quality_score' => 9,
                'review_id' => $review->id ?? null,
                'approval_status' => 'approved',
                'performance_id' => $performance->id ?? null,
                'safety_incident_id' => $incident->id ?? null,
                'submitted_by' => $admin['id'],
                'submitted_at' => $this->now->copy()->subHours(18),
                'notes' => 'Seeded quality review.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('material_returns')) {
            $this->insertIfEmpty('material_returns', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $firstWorkOrder['id'],
                'product_id' => $secondaryProduct['id'],
                'quantity' => 1,
                'return_date' => $this->now->copy()->subDay()->toDateString(),
                'returned_by' => $admin['id'],
                'reason' => 'Unused PPE returned to stock.',
                'notes' => 'Seeded material return.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('material_purchases') && $vendor) {
            $this->insertIfEmpty('material_purchases', [
                'id' => (string) Str::uuid(),
                'work_order_id' => $firstWorkOrder['id'],
                'product_id' => $product['id'],
                'vendor_id' => $vendor['id'],
                'quantity' => 8,
                'unit_cost' => 26.25,
                'total_cost' => 210.00,
                'purchase_date' => $this->now->copy()->subDays(5)->toDateString(),
                'receipt_url' => 'https://example.com/receipts/material-purchase.pdf',
                'notes' => 'Seeded emergency material purchase.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('contractor_availability') && $contractor) {
            $this->ensureRecord('contractor_availability', ['contractor_id' => $contractor['id'], 'availability_type' => 'recurring'], [
                'contractor_id' => $contractor['id'],
                'availability_type' => 'recurring',
                'day_of_week' => 1,
                'specific_date' => null,
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'is_available' => true,
                'notes' => 'Seeded Monday availability.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('booking_requests')) {
            $service = reset($services);
            $this->ensureRecord('booking_requests', ['request_number' => 'BR-1001'], [
                'request_number' => 'BR-1001',
                'contact_id' => $firstWorkOrder['contact_id'],
                'work_order_id' => $firstWorkOrder['id'],
                'service_id' => $service['id'] ?? null,
                'preferred_dates' => json_encode([$this->now->copy()->addDays(2)->toDateString(), $this->now->copy()->addDays(4)->toDateString()]),
                'requested_duration_minutes' => 180,
                'notes' => 'Customer requested morning window.',
                'status' => 'confirmed',
                'proposed_booking_id' => $bookings['WO-DEMO-1001']['id'] ?? null,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('sops')) {
            $sop = $this->ensureRecord('sops', ['title' => 'Exterior Wash Pre-Flight'], [
                'title' => 'Exterior Wash Pre-Flight',
                'description' => 'Checklist before dispatching an exterior wash crew.',
                'content' => 'Verify chemicals, water access, and customer access notes before departure.',
                'document_url' => 'https://example.com/sops/exterior-wash.pdf',
                'version' => '1.2',
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);

            if (Schema::hasTable('service_sops')) {
                $service = $services['Exterior House Wash'] ?? reset($services);
                $this->ensureRecord('service_sops', ['service_id' => $service['id'], 'sop_id' => $sop['id']], [
                    'service_id' => $service['id'],
                    'sop_id' => $sop['id'],
                    'is_required' => true,
                    'created_by' => $admin['id'],
                    'updated_by' => $admin['id'],
                ]);
            }
        }

        if (Schema::hasTable('work_order_assignments') && $contractor) {
            $this->ensureRecord('work_order_assignments', ['work_order_id' => $firstWorkOrder['id'], 'contractor_id' => $contractor['id']], [
                'work_order_id' => $firstWorkOrder['id'],
                'contractor_id' => $contractor['id'],
                'assigned_at' => $this->now->copy()->subDays(4),
                'notes' => 'Primary assignment record for seeded work order.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('customer_signoffs')) {
            $this->ensureRecord('customer_signoffs', ['work_order_id' => $completedWorkOrder['id']], [
                'work_order_id' => $completedWorkOrder['id'],
                'signed_by_name' => $completedWorkOrder['customer_name'],
                'signed_by_title' => 'Facilities Manager',
                'signature_url' => 'https://example.com/signoffs/wo-demo-1003-signature.png',
                'signoff_date' => $this->now->copy()->subDay()->toDateString(),
                'notes' => 'Customer approved closeout.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }
    }

    private function fetchChartAccounts(): array
    {
        if (! Schema::hasTable('chart_of_accounts')) {
            return [];
        }

        return DB::table('chart_of_accounts')->get()->keyBy('code')->map(fn ($row) => (array) $row)->all();
    }

    private function seedJournalEntries(array $users, array $accounts): array
    {
        if (! Schema::hasTable('journal_entries') || ! Schema::hasTable('journal_entry_lines') || $users === [] || $accounts === []) {
            return [];
        }

        $finance = $users['finance.lead@applab.test'] ?? reset($users);

        $invoiceEntry = $this->ensureRecord('journal_entries', ['entry_number' => 'JE-DEMO-2001'], [
            'entry_number' => 'JE-DEMO-2001',
            'entry_date' => $this->now->copy()->subDays(6)->toDateString(),
            'description' => 'Revenue recognition for demo invoice batch.',
            'source_module' => 'accounting',
            'total_debits' => 1850,
            'total_credits' => 1850,
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);

        $billEntry = $this->ensureRecord('journal_entries', ['entry_number' => 'JE-DEMO-2002'], [
            'entry_number' => 'JE-DEMO-2002',
            'entry_date' => $this->now->copy()->subDays(4)->toDateString(),
            'description' => 'Vendor payable recognition for seeded materials.',
            'source_module' => 'accounting',
            'total_debits' => 420,
            'total_credits' => 420,
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);

        $this->ensureRecord('journal_entry_lines', ['journal_entry_id' => $invoiceEntry['id'], 'account_id' => $accounts['1100']['id']], [
            'journal_entry_id' => $invoiceEntry['id'],
            'account_id' => $accounts['1100']['id'],
            'debit' => 1850,
            'credit' => 0,
            'description' => 'Accounts receivable seeded debit.',
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);
        $this->ensureRecord('journal_entry_lines', ['journal_entry_id' => $invoiceEntry['id'], 'account_id' => $accounts['4000']['id']], [
            'journal_entry_id' => $invoiceEntry['id'],
            'account_id' => $accounts['4000']['id'],
            'debit' => 0,
            'credit' => 1850,
            'description' => 'Service revenue seeded credit.',
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);
        $this->ensureRecord('journal_entry_lines', ['journal_entry_id' => $billEntry['id'], 'account_id' => $accounts['5000']['id']], [
            'journal_entry_id' => $billEntry['id'],
            'account_id' => $accounts['5000']['id'],
            'debit' => 420,
            'credit' => 0,
            'description' => 'Materials expense seeded debit.',
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);
        $this->ensureRecord('journal_entry_lines', ['journal_entry_id' => $billEntry['id'], 'account_id' => $accounts['2000']['id']], [
            'journal_entry_id' => $billEntry['id'],
            'account_id' => $accounts['2000']['id'],
            'debit' => 0,
            'credit' => 420,
            'description' => 'Accounts payable seeded credit.',
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);

        return [
            'invoice' => $invoiceEntry,
            'bill' => $billEntry,
        ];
    }

    private function seedAccounting(array $users, array $organizations, array $contacts, array $services, array $products, array $workOrders, array $journals): array
    {
        $result = ['invoices' => [], 'bills' => [], 'payments' => []];

        if ($users === []) {
            return $result;
        }

        $finance = $users['finance.lead@applab.test'] ?? reset($users);
        $customerContact = $contacts['morgan.diaz@example.com'] ?? reset($contacts);
        $customerOrg = collect($organizations)->firstWhere('id', $customerContact['organization_id']);
        $vendor = $organizations['Precision Supply House'] ?? reset($organizations);
        $service = $services['Exterior House Wash'] ?? reset($services);
        $product = $products['CHEM-SH-001'] ?? reset($products);
        $workOrder = $workOrders['WO-DEMO-1001'] ?? reset($workOrders);

        if (Schema::hasTable('invoices')) {
            $invoice = $this->ensureRecord('invoices', ['invoice_number' => 'INV-DEMO-2001'], [
                'invoice_number' => 'INV-DEMO-2001',
                'journal_entry_id' => $journals['invoice']['id'] ?? null,
                'work_order_id' => $workOrder['id'] ?? null,
                'contact_id' => $customerContact['id'],
                'contact_name' => $customerContact['name'],
                'organization_id' => $customerOrg['id'] ?? null,
                'organization_name' => $customerOrg['name'] ?? null,
                'issue_date' => $this->now->copy()->subDays(6)->toDateString(),
                'due_date' => $this->now->copy()->addDays(9)->toDateString(),
                'status' => 'partial',
                'subtotal' => 1725,
                'tax_amount' => 125,
                'total_amount' => 1850,
                'amount_paid' => 600,
                'amount_due' => 1250,
                'notes' => 'Seeded invoice for banking and accounting views.',
                'sent_at' => $this->now->copy()->subDays(5),
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
            $result['invoices']['INV-DEMO-2001'] = $invoice;

            if (Schema::hasTable('invoice_items')) {
                $this->ensureRecord('invoice_items', ['invoice_id' => $invoice['id'], 'description' => 'Exterior wash service package'], [
                    'invoice_id' => $invoice['id'],
                    'service_id' => $service['id'] ?? null,
                    'product_id' => null,
                    'description' => 'Exterior wash service package',
                    'quantity' => 1,
                    'unit_price' => 1600,
                    'total' => 1600,
                    'created_by' => $finance['id'],
                    'updated_by' => $finance['id'],
                ]);
                $this->ensureRecord('invoice_items', ['invoice_id' => $invoice['id'], 'description' => 'Cleaning materials surcharge'], [
                    'invoice_id' => $invoice['id'],
                    'service_id' => null,
                    'product_id' => $product['id'] ?? null,
                    'description' => 'Cleaning materials surcharge',
                    'quantity' => 1,
                    'unit_price' => 125,
                    'total' => 125,
                    'created_by' => $finance['id'],
                    'updated_by' => $finance['id'],
                ]);
            }

            if (Schema::hasColumn('work_orders', 'invoice_id') && $workOrder) {
                DB::table('work_orders')->where('id', $workOrder['id'])->update([
                    'invoice_id' => $invoice['id'],
                    'invoice_number' => $invoice['invoice_number'],
                    'updated_at' => $this->now,
                ]);
            }
        }

        if (Schema::hasTable('recurring_invoices')) {
            $this->ensureRecord('recurring_invoices', ['name' => 'Monthly Facility Retainer'], [
                'name' => 'Monthly Facility Retainer',
                'contact_id' => $customerContact['id'],
                'contact_name' => $customerContact['name'],
                'organization_id' => $customerOrg['id'] ?? null,
                'organization_name' => $customerOrg['name'] ?? null,
                'frequency' => 'monthly',
                'start_date' => $this->now->copy()->startOfMonth()->toDateString(),
                'end_date' => null,
                'next_invoice_date' => $this->now->copy()->addMonth()->startOfMonth()->toDateString(),
                'subtotal' => 2400,
                'tax_amount' => 0,
                'total_amount' => 2400,
                'status' => 'active',
                'last_generated_invoice_id' => $result['invoices']['INV-DEMO-2001']['id'] ?? null,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bills')) {
            $bill = $this->ensureRecord('bills', ['bill_number' => 'BILL-DEMO-2001'], [
                'bill_number' => 'BILL-DEMO-2001',
                'vendor_id' => $vendor['id'] ?? null,
                'vendor_name' => $vendor['name'] ?? 'Vendor',
                'issue_date' => $this->now->copy()->subDays(4)->toDateString(),
                'due_date' => $this->now->copy()->addDays(10)->toDateString(),
                'status' => 'approved',
                'subtotal' => 420,
                'tax_amount' => 0,
                'total_amount' => 420,
                'amount_paid' => 0,
                'notes' => 'Seeded bill for vendor AP workflow.',
                'journal_entry_id' => $journals['bill']['id'] ?? null,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
            $result['bills']['BILL-DEMO-2001'] = $bill;
        }

        if (Schema::hasTable('payments')) {
            $incoming = $this->ensureRecord('payments', ['payment_number' => 'PAY-DEMO-IN-2001'], [
                'payment_number' => 'PAY-DEMO-IN-2001',
                'journal_entry_id' => $journals['invoice']['id'] ?? null,
                'invoice_id' => $result['invoices']['INV-DEMO-2001']['id'] ?? null,
                'bill_id' => null,
                'contact_id' => $customerContact['id'],
                'organization_id' => $customerOrg['id'] ?? null,
                'amount' => 600,
                'payment_date' => $this->now->copy()->subDays(2)->toDateString(),
                'method' => 'bank_transfer',
                'reference' => 'ACH-778201',
                'payment_direction' => 'incoming',
                'reconciliation_status' => 'pending',
                'notes' => 'Partial payment received.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
            $outgoing = $this->ensureRecord('payments', ['payment_number' => 'PAY-DEMO-OUT-2001'], [
                'payment_number' => 'PAY-DEMO-OUT-2001',
                'journal_entry_id' => $journals['bill']['id'] ?? null,
                'invoice_id' => null,
                'bill_id' => $result['bills']['BILL-DEMO-2001']['id'] ?? null,
                'contact_id' => null,
                'organization_id' => $vendor['id'] ?? null,
                'amount' => 220,
                'payment_date' => $this->now->copy()->subDay()->toDateString(),
                'method' => 'credit_card',
                'reference' => 'CC-2201',
                'payment_direction' => 'outgoing',
                'reconciliation_status' => 'pending',
                'notes' => 'Partial vendor payment issued.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);

            $result['payments'] = [$incoming, $outgoing];
        }

        if (Schema::hasTable('expenses')) {
            $this->ensureRecord('expenses', ['expense_number' => 'EXP-DEMO-2001'], [
                'expense_number' => 'EXP-DEMO-2001',
                'vendor_id' => $vendor['id'] ?? null,
                'amount' => 180,
                'expense_date' => $this->now->copy()->subDays(3)->toDateString(),
                'category' => 'Fuel',
                'description' => 'Seeded fleet fuel expense.',
                'receipt_url' => 'https://example.com/receipts/fuel.pdf',
                'journal_entry_id' => $journals['bill']['id'] ?? null,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('payroll')) {
            $employee = $users['ops.manager@applab.test'] ?? $finance;
            $this->ensureRecord('payroll', ['payroll_number' => 'PAYROLL-DEMO-2001'], [
                'payroll_number' => 'PAYROLL-DEMO-2001',
                'employee_id' => $employee['id'],
                'pay_period_start' => $this->now->copy()->subWeeks(2)->startOfWeek()->toDateString(),
                'pay_period_end' => $this->now->copy()->subWeek()->endOfWeek()->toDateString(),
                'pay_date' => $this->now->copy()->subWeek()->endOfWeek()->toDateString(),
                'gross_pay' => 2400,
                'deductions' => 420,
                'net_pay' => 1980,
                'status' => 'paid',
                'notes' => 'Seeded payroll batch.',
                'journal_entry_id' => $journals['bill']['id'] ?? null,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('tax_forms')) {
            $vendorContact = $contacts['ap@precisionsupply.test'] ?? null;
            $this->insertIfEmpty('tax_forms', [
                'id' => (string) Str::uuid(),
                'vendor_organization_id' => $vendor['id'] ?? null,
                'vendor_contact_id' => $vendorContact['id'] ?? null,
                'tax_year' => (int) $this->now->year,
                'form_type' => '1099-NEC',
                'total_paid' => 12450,
                'tax_id' => '98-7654321',
                'address' => json_encode(['city' => 'Austin', 'state' => 'TX']),
                'status' => 'pending',
                'filed_date' => null,
                'sent_date' => null,
                'notes' => 'Seeded contractor tax form.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('tax_settings')) {
            $this->insertIfEmpty('tax_settings', [
                'id' => (string) Str::uuid(),
                'company_name' => 'APP-LAB Demo Co',
                'tax_id' => '12-3456789',
                'address' => json_encode(['street' => '500 Commerce Ave', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78702']),
                'contact_name' => 'Felix Ledger',
                'contact_phone' => '555-500-1001',
                'contact_email' => 'finance.lead@applab.test',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('tax_filings')) {
            $this->ensureRecord('tax_filings', ['tax_year' => $this->now->year, 'form_type' => 'Sales Tax Q2'], [
                'tax_year' => $this->now->year,
                'form_type' => 'Sales Tax Q2',
                'filing_date' => $this->now->copy()->addDays(20)->toDateString(),
                'status' => 'pending',
                'amount_due' => 920,
                'notes' => 'Seeded quarterly filing.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('tax_payments')) {
            $filing = DB::table('tax_filings')->first();
            $this->insertIfEmpty('tax_payments', [
                'id' => (string) Str::uuid(),
                'tax_filing_id' => $filing->id ?? null,
                'payment_date' => $this->now->copy()->subMonths(1)->toDateString(),
                'amount' => 780,
                'method' => 'ACH',
                'reference' => 'TAX-ACH-1001',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('credits')) {
            $this->ensureRecord('credits', ['credit_number' => 'CR-DEMO-2001'], [
                'credit_number' => 'CR-DEMO-2001',
                'credit_source' => 'customer',
                'invoice_id' => $result['invoices']['INV-DEMO-2001']['id'] ?? null,
                'work_order_id' => $workOrder['id'] ?? null,
                'product_id' => $product['id'] ?? null,
                'payment_id' => $result['payments'][0]['id'] ?? null,
                'contact_id' => $customerContact['id'],
                'vendor_id' => null,
                'amount' => 75,
                'quantity_returned' => 1,
                'credit_date' => $this->now->copy()->subDay()->toDateString(),
                'credit_type' => 'refund',
                'reason' => 'Minor service adjustment issued.',
                'status' => 'approved',
                'journal_entry_id' => $journals['invoice']['id'] ?? null,
                'notes' => 'Seeded credit memo.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        return $result;
    }

    private function seedTickets(array $users, array $contacts, array $organizations, array $workOrders, array $invoices): array
    {
        if (! Schema::hasTable('tickets') || $users === [] || $contacts === []) {
            return [];
        }

        $ops = $users['ops.manager@applab.test'] ?? reset($users);
        $contact = $contacts['talia.brooks@example.com'] ?? reset($contacts);
        $org = collect($organizations)->firstWhere('id', $contact['organization_id']);
        $workOrder = $workOrders['WO-DEMO-1002'] ?? reset($workOrders);
        $invoice = reset($invoices) ?: null;

        $tickets = [];

        $tickets['TICK-2001'] = $this->ensureRecord('tickets', ['ticket_number' => 'TICK-2001'], [
            'ticket_number' => 'TICK-2001',
            'title' => 'Reschedule requested by customer',
            'description' => 'Customer needs to move service from afternoon to morning.',
            'instructions' => 'Coordinate with dispatch and confirm by SMS.',
            'instructions_format' => 'plain_text',
            'category' => 'scheduling',
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'human',
            'assigned_to' => $ops['id'],
            'contact_id' => $contact['id'],
            'contact_name' => $contact['name'],
            'organization_id' => $org['id'] ?? null,
            'organization_name' => $org['name'] ?? null,
            'related_work_order_id' => $workOrder['id'] ?? null,
            'related_invoice_id' => $invoice['id'] ?? null,
            'notes' => json_encode(['seeded' => true]),
            'created_by' => $ops['id'],
            'updated_by' => $ops['id'],
        ]);

        return $tickets;
    }

    private function seedBanking(array $users, array $organizations, array $journals): array
    {
        $result = ['accounts' => [], 'cards' => [], 'transactions' => []];

        if ($users === [] || ! Schema::hasTable('bank_accounts')) {
            return $result;
        }

        $finance = $users['finance.lead@applab.test'] ?? reset($users);
        $vendor = $organizations['Precision Supply House'] ?? reset($organizations);

        $result['accounts']['Operating Checking'] = $this->ensureRecord('bank_accounts', ['name' => 'Operating Checking'], [
            'name' => 'Operating Checking',
            'account_type' => 'checking',
            'bank_name' => 'Mercury Demo Banking',
            'account_number' => '****1024',
            'currency' => 'USD',
            'current_balance' => 18640.75,
            'is_active' => true,
            'last_reconciled_date' => $this->now->copy()->subDays(7)->toDateString(),
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);
        $result['accounts']['Reserve Savings'] = $this->ensureRecord('bank_accounts', ['name' => 'Reserve Savings'], [
            'name' => 'Reserve Savings',
            'account_type' => 'savings',
            'bank_name' => 'Mercury Demo Banking',
            'account_number' => '****8841',
            'currency' => 'USD',
            'current_balance' => 9250.00,
            'is_active' => true,
            'last_reconciled_date' => $this->now->copy()->subDays(14)->toDateString(),
            'created_by' => $finance['id'],
            'updated_by' => $finance['id'],
        ]);

        if (Schema::hasTable('bank_cards')) {
            $result['cards']['Ops Fuel Card'] = $this->ensureRecord('bank_cards', ['mercury_card_id' => 'card_demo_ops_1001'], [
                'card_name' => 'Ops Fuel Card',
                'last4' => '4421',
                'mercury_card_id' => 'card_demo_ops_1001',
                'vendor_id' => $vendor['id'] ?? null,
                'bank_account_id' => $result['accounts']['Operating Checking']['id'],
                'daily_limit' => 500,
                'per_transaction_limit' => 200,
                'status' => 'active',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bank_transactions')) {
            $result['transactions']['BTX-2001'] = $this->ensureRecord('bank_transactions', ['reference' => 'BTX-2001'], [
                'bank_account_id' => $result['accounts']['Operating Checking']['id'],
                'card_id' => $result['cards']['Ops Fuel Card']['id'] ?? null,
                'transaction_date' => $this->now->copy()->subDays(2)->toDateString(),
                'amount' => 128.44,
                'transaction_type' => 'withdrawal',
                'description' => 'Fuel purchase for field crew',
                'reference' => 'BTX-2001',
                'external_category' => 'Fuel',
                'internal_category' => 'Vehicle Expense',
                'category_source' => 'manual',
                'status' => 'categorized',
                'journal_entry_id' => $journals['bill']['id'] ?? null,
                'notes' => 'Seeded transaction for banking UI.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bank_reconciliations')) {
            $this->ensureRecord('bank_reconciliations', ['bank_account_id' => $result['accounts']['Operating Checking']['id'], 'statement_date' => $this->now->copy()->endOfMonth()->toDateString()], [
                'bank_account_id' => $result['accounts']['Operating Checking']['id'],
                'statement_date' => $this->now->copy()->endOfMonth()->toDateString(),
                'statement_balance' => 18640.75,
                'book_balance' => 18512.31,
                'difference' => 128.44,
                'status' => 'discrepancies',
                'notes' => 'One transaction remains unreconciled.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bank_transfers')) {
            $this->ensureRecord('bank_transfers', ['transfer_number' => 'TRF-2001'], [
                'transfer_number' => 'TRF-2001',
                'from_account_id' => $result['accounts']['Operating Checking']['id'],
                'to_account_id' => $result['accounts']['Reserve Savings']['id'],
                'amount' => 1500,
                'transfer_date' => $this->now->copy()->subDays(8)->toDateString(),
                'status' => 'completed',
                'description' => 'Weekly reserve sweep.',
                'journal_entry_id' => $journals['invoice']['id'] ?? null,
                'notes' => 'Seeded inter-account transfer.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bank_imports')) {
            $this->ensureRecord('bank_imports', ['bank_account_id' => $result['accounts']['Operating Checking']['id'], 'file_name' => 'operating-checking-may.csv'], [
                'bank_account_id' => $result['accounts']['Operating Checking']['id'],
                'import_date' => $this->now->copy()->subDay()->toDateString(),
                'file_name' => 'operating-checking-may.csv',
                'file_type' => 'csv',
                'status' => 'processed',
                'total_transactions' => 24,
                'notes' => 'Seeded bank import.',
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        if (Schema::hasTable('bank_rules')) {
            $this->ensureRecord('bank_rules', ['rule_name' => 'Fuel purchases to Vehicle Expense'], [
                'rule_name' => 'Fuel purchases to Vehicle Expense',
                'priority' => 10,
                'conditions' => json_encode(['merchant_contains' => 'fuel']),
                'action' => json_encode(['set_internal_category' => 'Vehicle Expense']),
                'is_active' => true,
                'created_by' => $finance['id'],
                'updated_by' => $finance['id'],
            ]);
        }

        return $result;
    }

    private function seedContent(array $users, array $workOrders, array $products): array
    {
        $result = ['images' => [], 'assets' => []];

        if ($users === []) {
            return $result;
        }

        $admin = $users['admin@admin.com'];
        $workOrder = $workOrders['WO-DEMO-1001'] ?? reset($workOrders);
        $product = reset($products) ?: null;

        if (Schema::hasTable('blog_categories')) {
            $this->ensureRecord('blog_categories', ['slug' => 'operations-insights'], [
                'name' => 'Operations Insights',
                'slug' => 'operations-insights',
                'description' => 'Field ops, scheduling, and service execution content.',
                'color' => '#0f766e',
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('blog_tags')) {
            $this->ensureRecord('blog_tags', ['slug' => 'process-improvement'], [
                'name' => 'Process Improvement',
                'slug' => 'process-improvement',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('blog_posts')) {
            $category = DB::table('blog_categories')->where('slug', 'operations-insights')->first();
            $post = $this->ensureRecord('blog_posts', ['slug' => 'how-we-tightened-dispatch-windows'], [
                'title' => 'How We Tightened Dispatch Windows Without Burning Crew Time',
                'slug' => 'how-we-tightened-dispatch-windows',
                'excerpt' => 'A seeded post for content and blog list testing.',
                'content' => 'This seeded post exists to populate content views and filtering surfaces in the admin panel.',
                'featured_image_url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80',
                'author_id' => $admin['id'],
                'category_id' => $category->id ?? null,
                'category' => $category->name ?? 'Operations Insights',
                'status' => 'published',
                'published_at' => $this->now->copy()->subDays(7),
                'seo_title' => 'Dispatch Window Optimization',
                'seo_description' => 'Seeded blog article for operations insight.',
                'seo_keywords' => json_encode(['dispatch', 'operations', 'field service']),
                'reading_time_minutes' => 5,
                'view_count' => 128,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);

            if (Schema::hasTable('blog_post_tags')) {
                $tag = DB::table('blog_tags')->where('slug', 'process-improvement')->first();
                if ($tag) {
                    $this->ensureRecord('blog_post_tags', ['blog_post_id' => $post['id'], 'blog_tag_id' => $tag->id], [
                        'blog_post_id' => $post['id'],
                        'blog_tag_id' => $tag->id,
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }
            }
        }

        if (Schema::hasTable('page_section_types')) {
            $this->ensureRecord('page_section_types', ['slug' => 'hero-banner'], [
                'name' => 'Hero Banner',
                'slug' => 'hero-banner',
                'fields' => json_encode(['headline', 'subheadline', 'cta']),
                'description' => 'Seeded section definition for the page builder.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('pages')) {
            $this->ensureRecord('pages', ['page_slug' => 'commercial-cleaning'], [
                'page_slug' => 'commercial-cleaning',
                'page_title' => 'Commercial Cleaning',
                'status' => 'published',
                'meta_title' => 'Commercial Cleaning Services',
                'meta_description' => 'Seeded page for content module validation.',
                'sections' => json_encode([['type' => 'hero-banner', 'headline' => 'Commercial cleaning that keeps crews on schedule']]),
                'is_published' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('image_files')) {
            $result['images']['hero'] = $this->ensureRecord('image_files', ['filename' => 'seeded-hero-image.jpg'], [
                'filename' => 'seeded-hero-image.jpg',
                'original_filename' => 'hero.jpg',
                'file_url' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=400&q=80',
                'medium_url' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=800&q=80',
                'width' => 1200,
                'height' => 800,
                'mime_type' => 'image/jpeg',
                'size_bytes' => 210345,
                'tags' => json_encode(['hero', 'marketing']),
                'library_type' => 'content',
                'work_order_id' => $workOrder['id'] ?? null,
                'uploaded_by' => $admin['id'],
                'alt_text' => 'Seeded hero image',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('social_media_accounts')) {
            $this->ensureRecord('social_media_accounts', ['account_name' => 'APP-LAB Instagram'], [
                'platform' => 'instagram',
                'account_name' => 'APP-LAB Instagram',
                'username' => '@applabdemo',
                'account_type' => 'business',
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('social_media_content')) {
            $this->ensureRecord('social_media_content', ['platform' => 'instagram', 'caption' => 'Crew wrapped another spotless storefront refresh.'], [
                'image_id' => $result['images']['hero']['id'] ?? null,
                'platform' => 'instagram',
                'crop_url' => 'https://example.com/crops/instagram-hero.jpg',
                'caption' => 'Crew wrapped another spotless storefront refresh.',
                'hashtags' => json_encode(['fieldops', 'cleaningcrew', 'beforeandafter']),
                'scheduled_at' => $this->now->copy()->addDay(),
                'status' => 'scheduled',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('assets')) {
            $result['assets']['doorhanger'] = $this->ensureRecord('assets', ['filename' => 'seeded-doorhanger.pdf'], [
                'filename' => 'seeded-doorhanger.pdf',
                'original_filename' => 'doorhanger.pdf',
                'file_url' => 'https://example.com/assets/doorhanger.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 89012,
                'width' => null,
                'height' => null,
                'tags' => json_encode(['print', 'campaign']),
                'alt_text' => 'Seeded door hanger asset',
                'uploaded_by' => $admin['id'],
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('physical_designs')) {
            $design = $this->ensureRecord('physical_designs', ['name' => 'Spring Promo Door Hanger'], [
                'name' => 'Spring Promo Door Hanger',
                'design_type' => 'door_hanger',
                'description' => 'Seeded print design for field handoff.',
                'files' => json_encode(['pdf' => 'https://example.com/assets/doorhanger.pdf']),
                'dimensions' => '4.25x11',
                'status' => 'approved',
                'latest_version_id' => null,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);

            if (Schema::hasTable('physical_design_versions')) {
                $version = $this->ensureRecord('physical_design_versions', ['physical_design_id' => $design['id'], 'version_number' => 'v1'], [
                    'physical_design_id' => $design['id'],
                    'version_number' => 'v1',
                    'files' => json_encode(['print' => 'https://example.com/assets/doorhanger-v1.pdf']),
                    'notes' => 'Approved version for demo data.',
                    'status' => 'approved',
                    'created_by' => $admin['id'],
                    'updated_by' => $admin['id'],
                ]);

                DB::table('physical_designs')->where('id', $design['id'])->update([
                    'latest_version_id' => $version['id'],
                    'updated_at' => $this->now,
                ]);

                if (Schema::hasTable('product_designs') && $product) {
                    $this->ensureRecord('product_designs', ['product_id' => $product['id'], 'physical_design_id' => $design['id']], [
                        'product_id' => $product['id'],
                        'physical_design_id' => $design['id'],
                        'is_default' => true,
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }
            }
        }

        return $result;
    }

    private function seedMarketing(array $users, array $images): array
    {
        $result = ['campaign' => null, 'ad' => null, 'funnel' => null];

        if ($users === []) {
            return $result;
        }

        $admin = $users['admin@admin.com'];
        $image = $images['hero']['id'] ?? null;

        if (Schema::hasTable('ad_campaigns')) {
            $result['campaign'] = $this->ensureRecord('ad_campaigns', ['name' => 'Spring Commercial Push'], [
                'name' => 'Spring Commercial Push',
                'description' => 'Seeded campaign for marketing dashboards.',
                'platform' => 'facebook',
                'status' => 'active',
                'total_budget' => 5000,
                'amount_spent' => 1840,
                'roas' => 4.2,
                'start_date' => $this->now->copy()->subWeeks(2)->toDateString(),
                'end_date' => $this->now->copy()->addWeeks(2)->toDateString(),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('campaign_budgets') && $result['campaign']) {
            $this->ensureRecord('campaign_budgets', ['campaign_id' => $result['campaign']['id']], [
                'campaign_id' => $result['campaign']['id'],
                'total_budget' => 5000,
                'daily_budget' => 175,
                'amount_spent' => 1840,
                'remaining_budget' => 3160,
                'budget_status' => 'on_track',
                'start_date' => $this->now->copy()->subWeeks(2)->toDateString(),
                'end_date' => $this->now->copy()->addWeeks(2)->toDateString(),
                'notes' => 'Seeded campaign budget.',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('funnels')) {
            $result['funnel'] = $this->ensureRecord('funnels', ['name' => 'Commercial Quote Funnel'], [
                'name' => 'Commercial Quote Funnel',
                'description' => 'Seeded funnel for ad attribution testing.',
                'steps' => json_encode(['Ad Click', 'Landing Page', 'Quote Request', 'Sales Call']),
                'status' => 'active',
                'overall_conversion_rate' => 7.25,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('landing_pages')) {
            $landing = $this->ensureRecord('landing_pages', ['slug' => 'spring-commercial-offer'], [
                'name' => 'Spring Commercial Offer',
                'slug' => 'spring-commercial-offer',
                'title' => 'Spring Commercial Cleaning Offer',
                'meta_description' => 'Seeded landing page for campaign testing.',
                'content' => json_encode(['headline' => 'Book your spring refresh', 'cta' => 'Get a quote']),
                'ad_id' => null,
                'ad_count' => 1,
                'conversion_rate' => 6.4,
                'status' => 'published',
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);

            if (Schema::hasTable('ads')) {
                $result['ad'] = $this->ensureRecord('ads', ['name' => 'Spring Commercial Carousel'], [
                    'name' => 'Spring Commercial Carousel',
                    'platform' => 'facebook',
                    'campaign_id' => $result['campaign']['id'] ?? null,
                    'funnel_id' => $result['funnel']['id'] ?? null,
                    'headline' => 'Reliable crews for commercial properties',
                    'hook' => 'Faster response and cleaner closeouts.',
                    'description' => 'Seeded ad for campaign QA.',
                    'image_id' => $image,
                    'cta_text' => 'Request Quote',
                    'cta_url' => 'https://example.com/spring-commercial-offer',
                    'landing_page_id' => $landing['id'],
                    'status' => 'active',
                    'budget' => 2500,
                    'performance_score' => 81.5,
                    'roas' => 4.1,
                    'start_date' => $this->now->copy()->subWeeks(2)->toDateString(),
                    'end_date' => $this->now->copy()->addWeeks(2)->toDateString(),
                    'created_by' => $admin['id'],
                    'updated_by' => $admin['id'],
                ]);

                DB::table('landing_pages')->where('id', $landing['id'])->update([
                    'ad_id' => $result['ad']['id'],
                    'updated_at' => $this->now,
                ]);

                if (Schema::hasTable('ad_variants')) {
                    $this->ensureRecord('ad_variants', ['ad_id' => $result['ad']['id'], 'variant_name' => 'A'], [
                        'ad_id' => $result['ad']['id'],
                        'variant_name' => 'A',
                        'headline' => 'Reliable crews for commercial properties',
                        'hook' => 'Faster response and cleaner closeouts.',
                        'image_id' => $image,
                        'cta_text' => 'Request Quote',
                        'performance_score' => 81.5,
                        'is_winner' => true,
                        'status' => 'active',
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }

                if (Schema::hasTable('campaign_performance') && $result['campaign']) {
                    $this->ensureRecord('campaign_performance', ['campaign_id' => $result['campaign']['id'], 'date' => $this->now->copy()->subDay()->toDateString()], [
                        'campaign_id' => $result['campaign']['id'],
                        'date' => $this->now->copy()->subDay()->toDateString(),
                        'impressions' => 18420,
                        'clicks' => 612,
                        'conversions' => 44,
                        'cost' => 320.50,
                        'revenue' => 1490.00,
                        'ctr' => 0.0332,
                        'cpc' => 0.5237,
                        'roas' => 4.65,
                        'metrics' => json_encode(['qualified_calls' => 12]),
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }

                if (Schema::hasTable('ad_creative_assets')) {
                    $this->ensureRecord('ad_creative_assets', ['ad_id' => $result['ad']['id'], 'asset_name' => 'Primary Carousel Image'], [
                        'ad_id' => $result['ad']['id'],
                        'asset_name' => 'Primary Carousel Image',
                        'asset_type' => 'image',
                        'image_id' => $image,
                        'video_url' => null,
                        'headline' => 'Reliable crews for commercial properties',
                        'description' => 'Seeded creative asset.',
                        'cta_text' => 'Request Quote',
                        'performance_score' => 79.4,
                        'is_active' => true,
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }

                if (Schema::hasTable('ad_placements')) {
                    $this->ensureRecord('ad_placements', ['ad_id' => $result['ad']['id'], 'placement' => 'Facebook Feed'], [
                        'ad_id' => $result['ad']['id'],
                        'platform' => 'facebook',
                        'placement' => 'Facebook Feed',
                        'impressions' => 10210,
                        'clicks' => 331,
                        'cost' => 182.10,
                        'created_by' => $admin['id'],
                        'updated_by' => $admin['id'],
                    ]);
                }
            }
        }

        if (Schema::hasTable('ad_audiences')) {
            $this->ensureRecord('ad_audiences', ['name' => 'Commercial Property Managers'], [
                'name' => 'Commercial Property Managers',
                'platform' => 'facebook',
                'audience_type' => 'interest',
                'description' => 'Seeded audience segment.',
                'size_estimate' => 85000,
                'status' => 'active',
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('marketing_attribution') && $result['campaign'] && $result['ad']) {
            $contact = DB::table('contacts')->where('email', 'morgan.diaz@example.com')->first();
            $this->insertIfEmpty('marketing_attribution', [
                'id' => (string) Str::uuid(),
                'contact_id' => $contact->id ?? null,
                'ad_id' => $result['ad']['id'],
                'campaign_id' => $result['campaign']['id'],
                'funnel_id' => $result['funnel']['id'] ?? null,
                'touchpoint' => 'facebook_click',
                'touchpoint_date' => $this->now->copy()->subDays(9),
                'conversion_value' => 4200,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $result;
    }

    private function seedIntegrations(array $users): array
    {
        $result = ['integration' => null, 'webhook' => null];

        if ($users === []) {
            return $result;
        }

        $admin = $users['admin@admin.com'];

        if (Schema::hasTable('integrations')) {
            $result['integration'] = $this->ensureRecord('integrations', ['name' => 'Mercury Banking'], [
                'name' => 'Mercury Banking',
                'type' => 'api',
                'description' => 'Seeded banking integration.',
                'status' => 'active',
                'last_connected_at' => $this->now->copy()->subHours(6),
                'configuration' => json_encode(['environment' => 'sandbox']),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('api_credentials') && $result['integration']) {
            $this->ensureRecord('api_credentials', ['integration_id' => $result['integration']['id'], 'credential_name' => 'api_key'], [
                'integration_id' => $result['integration']['id'],
                'environment' => 'sandbox',
                'credential_name' => 'api_key',
                'credential_value' => 'seeded-demo-key',
                'is_active' => true,
                'expires_at' => $this->now->copy()->addYear(),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('webhooks') && $result['integration']) {
            $result['webhook'] = $this->ensureRecord('webhooks', ['integration_id' => $result['integration']['id'], 'event_type' => 'bank.transaction.updated'], [
                'integration_id' => $result['integration']['id'],
                'direction' => 'incoming',
                'event_type' => 'bank.transaction.updated',
                'endpoint_url' => 'https://example.com/hooks/bank-transactions',
                'secret' => 'seeded-webhook-secret',
                'status' => 'active',
                'is_active' => true,
                'last_triggered_at' => $this->now->copy()->subHours(3),
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('integration_logs') && $result['integration']) {
            $this->insertIfEmpty('integration_logs', [
                'id' => (string) Str::uuid(),
                'integration_id' => $result['integration']['id'],
                'webhook_id' => $result['webhook']['id'] ?? null,
                'log_type' => 'sync',
                'status' => 'success',
                'endpoint' => '/transactions/sync',
                'request_payload' => json_encode(['cursor' => 'seed-1']),
                'response_payload' => json_encode(['imported' => 24]),
                'error_message' => null,
                'duration_ms' => 842,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('snippets')) {
            $this->ensureRecord('snippets', ['name' => 'Analytics Footer Loader'], [
                'name' => 'Analytics Footer Loader',
                'description' => 'Seeded footer analytics snippet.',
                'code' => '<script>console.log("Seeded analytics snippet loaded");</script>',
                'snippet_type' => 'javascript',
                'placement' => 'footer',
                'page_slug' => null,
                'status' => 'active',
                'is_active' => true,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        return $result;
    }

    private function seedTasksAndAi(array $users, array $leads, array $opportunities, array $contacts, array $organizations, array $tickets, array $workOrders): array
    {
        $result = ['tasks' => []];

        if ($users === [] || ! Schema::hasTable('tasks')) {
            return $result;
        }

        $ops = $users['ops.manager@applab.test'] ?? reset($users);
        $ai = $users['automation.agent@applab.test'] ?? end($users);
        $lead = reset($leads) ?: null;
        $opportunity = reset($opportunities) ?: null;
        $contact = reset($contacts) ?: null;
        $organization = reset($organizations) ?: null;
        $ticket = reset($tickets) ?: null;
        $workOrder = reset($workOrders) ?: null;

        $result['tasks']['Dispatch follow-up'] = $this->ensureRecord('tasks', ['title' => 'Dispatch follow-up for demo schedule change'], [
            'title' => 'Dispatch follow-up for demo schedule change',
            'description' => 'Confirm revised arrival window with customer and crew.',
            'instructions' => 'Text customer, update booking note, and notify field crew.',
            'instructions_format' => 'plain_text',
            'category' => 'operations',
            'type' => 'dispatch_follow_up',
            'assigned_to' => $ops['id'],
            'assigned_to_name' => $ops['full_name'],
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => $this->now->copy()->addHours(6),
            'related_type' => 'work_order',
            'related_id' => $workOrder['id'] ?? null,
            'related_lead_id' => $lead['id'] ?? null,
            'related_opportunity_id' => $opportunity['id'] ?? null,
            'related_contact_id' => $contact['id'] ?? null,
            'related_organization_id' => $organization['id'] ?? null,
            'related_ticket_id' => $ticket['id'] ?? null,
            'related_work_order_id' => $workOrder['id'] ?? null,
            'notes' => json_encode(['seeded' => true]),
            'metadata' => json_encode(['channel' => 'sms']),
            'requires_human_approval' => false,
            'approved_by' => null,
            'approved_at' => null,
            'completed_at' => null,
            'created_by' => $ops['id'],
            'updated_by' => $ops['id'],
        ]);

        if (Schema::hasTable('ai_agent_profiles')) {
            $this->ensureRecord('ai_agent_profiles', ['user_id' => $ai['id']], [
                'user_id' => $ai['id'],
                'prompt_template' => 'Handle operational triage and summarize blockers.',
                'allowed_modules' => json_encode(['operations', 'crm', 'integrations']),
                'max_concurrency' => 3,
                'status' => 'active',
                'last_active_at' => $this->now->copy()->subMinutes(10),
                'tasks_completed_today' => 6,
                'avg_task_duration_seconds' => 92,
                'created_by' => $ops['id'],
                'updated_by' => $ops['id'],
            ]);
        }

        if (Schema::hasTable('ai_task_runs')) {
            $this->insertIfEmpty('ai_task_runs', [
                'id' => (string) Str::uuid(),
                'task_id' => $result['tasks']['Dispatch follow-up']['id'],
                'agent_user_id' => $ai['id'],
                'status' => 'completed',
                'started_at' => $this->now->copy()->subMinutes(25),
                'ended_at' => $this->now->copy()->subMinutes(23),
                'duration_seconds' => 120,
                'execution_log' => json_encode(['steps' => ['loaded work order', 'checked crew availability', 'prepared summary']]),
                'error_message' => null,
                'output_summary' => 'Crew and customer are both available for the revised window.',
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $ops['id'],
                'updated_by' => $ops['id'],
            ]);
        }

        if (Schema::hasTable('ai_alerts')) {
            $this->insertIfEmpty('ai_alerts', [
                'id' => (string) Str::uuid(),
                'task_id' => $result['tasks']['Dispatch follow-up']['id'],
                'agent_user_id' => $ai['id'],
                'alert_type' => 'approval_required',
                'severity' => 'medium',
                'message' => 'Customer requested a same-day adjustment outside normal dispatch policy.',
                'is_resolved' => false,
                'resolved_at' => null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $ops['id'],
                'updated_by' => $ops['id'],
            ]);
        }

        return $result;
    }

    private function seedAdministration(array $users, array $inventory, array $products, array $tasks, array $settings, array $company, array $organizations, array $contacts, array $services, array $workOrders, array $invoices, array $integrations): void
    {
        if ($users === []) {
            return;
        }

        $admin = $users['admin@admin.com'];

        if (Schema::hasTable('change_log')) {
            $workOrder = reset($workOrders) ?: null;
            $this->insertIfEmpty('change_log', [
                'id' => (string) Str::uuid(),
                'table_name' => 'work_orders',
                'record_id' => $workOrder['id'] ?? $admin['id'],
                'action' => 'automation',
                'user_id' => $admin['id'],
                'changes' => json_encode(['status' => ['scheduled', 'in_progress']]),
                'metadata' => json_encode(['source' => 'comprehensive-demo-seeder']),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('system_health_events')) {
            $this->insertIfEmpty('system_health_events', [
                'id' => (string) Str::uuid(),
                'event_type' => 'low_stock',
                'severity' => 'warning',
                'title' => 'Soft Wash Detergent below preferred stock threshold',
                'details' => json_encode(['sku' => 'CONS-NOZZLE-001', 'warehouse' => 'Warehouse A']),
                'related_table_name' => 'inventory',
                'related_record_id' => $inventory['CONS-NOZZLE-001']['id'] ?? null,
                'resolved_by' => null,
                'resolved_at' => null,
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }

        if (Schema::hasTable('low_stock_alerts')) {
            $inv = $inventory['CONS-NOZZLE-001'] ?? reset($inventory);
            $product = $products['CONS-NOZZLE-001'] ?? reset($products);
            $task = $tasks['tasks']['Dispatch follow-up'] ?? null;
            $this->ensureRecord('low_stock_alerts', ['inventory_id' => $inv['id']], [
                'inventory_id' => $inv['id'],
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'quantity_on_hand' => $inv['quantity_on_hand'],
                'reorder_level' => $inv['reorder_level'],
                'status' => 'open',
                'related_task_id' => $task['id'] ?? null,
                'resolved_at' => null,
                'created_by' => $admin['id'],
                'updated_by' => $admin['id'],
            ]);
        }
    }

    private function ensureRecord(string $table, array $lookup, array $values): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $existing = DB::table($table)->where($lookup)->first();
        $timestampedValues = $this->stamp($values);

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update($timestampedValues);

            return (array) DB::table($table)->where('id', $existing->id)->first();
        }

        $record = [
            'id' => (string) Str::uuid(),
            ...$timestampedValues,
        ];

        DB::table($table)->insert($record);

        return $record;
    }

    private function insertIfEmpty(string $table, array $record): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (DB::table($table)->exists()) {
            return;
        }

        DB::table($table)->insert($this->stamp($record));
    }

    private function stamp(array $values): array
    {
        if (! array_key_exists('created_at', $values)) {
            $values['created_at'] = $this->now;
        }

        if (! array_key_exists('updated_at', $values)) {
            $values['updated_at'] = $this->now;
        }

        return $values;
    }
}