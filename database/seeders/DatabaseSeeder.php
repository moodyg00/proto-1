<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'full_name'     => 'Admin User',
                'username'      => 'admin',
                'password_hash' => \Illuminate\Support\Facades\Hash::make('admin'),
                'user_type'     => 'human',
                'role'          => 'admin',
                'is_active'     => true,
            ]
        );

        $this->call(ChartOfAccountsSeeder::class);
        $this->call(SampleLeadsSeeder::class);
        $this->call(SampleWorkOrdersSeeder::class);
        $this->call(ComprehensiveDemoSeeder::class);
    }
}
