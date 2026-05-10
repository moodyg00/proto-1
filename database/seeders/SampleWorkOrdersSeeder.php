<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class SampleWorkOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::query()->where('email', 'admin@admin.com')->first();

        $customerA = Contact::query()->updateOrCreate(
            ['email' => 'sample.customer.one@applab.test'],
            [
                'name' => 'Sample Customer One',
                'type' => 'customer',
                'status' => 'active',
                'phone' => '555-0101',
            ],
        );

        $customerB = Contact::query()->updateOrCreate(
            ['email' => 'sample.customer.two@applab.test'],
            [
                'name' => 'Sample Customer Two',
                'type' => 'customer',
                'status' => 'active',
                'phone' => '555-0102',
            ],
        );

        $contractor = Contact::query()->updateOrCreate(
            ['email' => 'sample.contractor@applab.test'],
            [
                'name' => 'Sample Contractor',
                'type' => 'contractor',
                'status' => 'active',
                'phone' => '555-0199',
            ],
        );

        $records = [
            [
                'work_order_number' => 'WO-DEMO-1001',
                'contact_id' => $customerA->id,
                'customer_name' => $customerA->name,
                'invoice_number' => 'INV-DEMO-1001',
                'service_name' => 'Exterior house wash',
                'assigned_contractor_id' => $contractor->id,
                'assigned_contractor' => $contractor->name,
                'contractor_status' => 'accepted',
                'status' => 'scheduled',
                'scheduled_date' => now()->addDay()->toDateString(),
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '09:00:00',
                'address' => [
                    'street' => '101 Demo Street',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                ],
                'special_instructions' => 'Gate code is 1234. Call ahead before arrival.',
                'notes' => ['source' => 'sample_seed', 'priority' => 'standard'],
            ],
            [
                'work_order_number' => 'WO-DEMO-1002',
                'contact_id' => $customerB->id,
                'customer_name' => $customerB->name,
                'invoice_number' => 'INV-DEMO-1002',
                'service_name' => 'Driveway cleaning',
                'assigned_contractor_id' => $contractor->id,
                'assigned_contractor' => $contractor->name,
                'contractor_status' => 'pending',
                'status' => 'scheduled',
                'scheduled_date' => now()->addDays(3)->toDateString(),
                'booking_date' => now()->addDays(3)->toDateString(),
                'booking_time' => '13:30:00',
                'address' => [
                    'street' => '202 Sample Avenue',
                    'city' => 'Dallas',
                    'state' => 'TX',
                    'postal_code' => '75201',
                ],
                'special_instructions' => 'Customer prefers afternoon service window.',
                'notes' => ['source' => 'sample_seed', 'priority' => 'high'],
            ],
            [
                'work_order_number' => 'WO-DEMO-1003',
                'contact_id' => $customerA->id,
                'customer_name' => $customerA->name,
                'invoice_number' => 'INV-DEMO-1003',
                'service_name' => 'Roof treatment follow-up',
                'assigned_contractor_id' => $contractor->id,
                'assigned_contractor' => $contractor->name,
                'contractor_status' => 'accepted',
                'status' => 'completed',
                'scheduled_date' => now()->subDays(2)->toDateString(),
                'booking_date' => now()->subDays(2)->toDateString(),
                'booking_time' => '10:15:00',
                'completed_at' => now()->subDays(2)->setTime(12, 15),
                'address' => [
                    'street' => '101 Demo Street',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                ],
                'special_instructions' => 'Confirm before/after photos are attached to the job.',
                'notes' => ['source' => 'sample_seed', 'priority' => 'follow_up'],
            ],
        ];

        foreach ($records as $record) {
            WorkOrder::query()->updateOrCreate(
                ['work_order_number' => $record['work_order_number']],
                [
                    ...$record,
                    'created_by' => $adminUser?->id,
                    'updated_by' => $adminUser?->id,
                ],
            );
        }
    }
}