<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleLeadsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()
            ->where('email', 'admin@applab.test')
            ->orWhere('email', 'admin@admin.com')
            ->first();

        if (! $owner) {
            return;
        }

        $leadRows = [
            [
                'name' => 'Morgan Diaz',
                'email' => 'morgan.diaz@example.com',
                'phone' => '555-120-1001',
                'title' => 'Quarterly office deep clean',
                'organization' => 'Northstar Legal Group',
                'status' => 'uncontacted',
                'source' => 'referral',
                'expected_value' => 4200,
            ],
            [
                'name' => 'Talia Brooks',
                'email' => 'talia.brooks@example.com',
                'phone' => '555-120-1002',
                'title' => 'Retail storefront maintenance',
                'organization' => 'Harborline Outfitters',
                'status' => 'contacted',
                'source' => 'facebook',
                'expected_value' => 6800,
            ],
            [
                'name' => 'Seth Coleman',
                'email' => 'seth.coleman@example.com',
                'phone' => '555-120-1003',
                'title' => 'HVAC service proposal',
                'organization' => 'Pine & Ash Properties',
                'status' => 'quoted',
                'source' => 'website_organic',
                'expected_value' => 9100,
            ],
            [
                'name' => 'Avery Patel',
                'email' => 'avery.patel@example.com',
                'phone' => '555-120-1004',
                'title' => 'Multi-site janitorial agreement',
                'organization' => 'Blue Ferry Medical',
                'status' => 'booked',
                'source' => 'nextdoor',
                'expected_value' => 12500,
            ],
            [
                'name' => 'Nina Walsh',
                'email' => 'nina.walsh@example.com',
                'phone' => '555-120-1005',
                'title' => 'Recurring condo common-area service',
                'organization' => 'Cedar Crest HOA',
                'status' => 'converted',
                'source' => 'instagram',
                'expected_value' => 5300,
            ],
            [
                'name' => 'Caleb Nguyen',
                'email' => 'caleb.nguyen@example.com',
                'phone' => '555-120-1006',
                'title' => 'Warehouse floor restoration',
                'organization' => 'Atlas Food Supply',
                'status' => 'lost',
                'source' => 'physical_media',
                'expected_value' => 7600,
            ],
        ];

        foreach ($leadRows as $index => $leadRow) {
            $organization = Organization::query()->updateOrCreate(
                ['name' => $leadRow['organization']],
                [
                    'phone' => $leadRow['phone'],
                    'relationship_type' => 'lead',
                    'organization_type' => 'company',
                    'status' => 'active',
                    'source' => $leadRow['source'],
                    'created_by' => $owner->getKey(),
                    'updated_by' => $owner->getKey(),
                ],
            );

            $contact = Contact::query()->updateOrCreate(
                ['email' => $leadRow['email']],
                [
                    'name' => $leadRow['name'],
                    'title' => 'Primary Contact',
                    'type' => 'lead',
                    'phone' => $leadRow['phone'],
                    'organization_id' => $organization->getKey(),
                    'status' => 'active',
                    'source' => $leadRow['source'],
                    'created_by' => $owner->getKey(),
                    'updated_by' => $owner->getKey(),
                ],
            );

            Lead::query()->updateOrCreate(
                ['email' => $leadRow['email']],
                [
                    'contact_id' => $contact->getKey(),
                    'organization_id' => $organization->getKey(),
                    'name' => $leadRow['name'],
                    'phone' => $leadRow['phone'],
                    'title' => $leadRow['title'],
                    'source' => $leadRow['source'],
                    'status' => $leadRow['status'],
                    'assigned_to' => $owner->getKey(),
                    'next_follow_up' => now()->addDays($index + 1),
                    'last_contacted_at' => in_array($leadRow['status'], ['contacted', 'quoted', 'booked', 'converted'], true) ? now()->subDays($index + 1) : null,
                    'expected_value' => $leadRow['expected_value'],
                    'closed_at' => in_array($leadRow['status'], ['converted', 'lost'], true) ? now()->subDay() : null,
                    'lost_reason' => $leadRow['status'] === 'lost' ? 'Budget moved to an internal team' : null,
                    'converted_at' => $leadRow['status'] === 'converted' ? now()->subDay() : null,
                    'created_by' => $owner->getKey(),
                    'updated_by' => $owner->getKey(),
                ],
            );
        }
    }
}