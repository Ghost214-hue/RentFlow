<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin accounts.
     */
    public function run(): void
    {
        // Check if admin already exists to avoid duplicate entries
        if (Admin::where('email', 'karenjuduncan750@gmail.com')->exists()) {
            $this->command->info('Admin already exists. Skipping creation.');
            return;
        }

        $admin = Admin::create([
            'name' => 'Karen Duncan',
            'email' => 'karenjuduncan750@gmail.com',
            'password' => Hash::make('karenjuduncan750!', ['rounds' => 12]),
            'status' => 'active',
            'admin_role' => 'super_admin',
            'two_factor_required' => true,
        ]);

        $this->command->info("Admin created successfully:");
        $this->command->line("  Email: karenjuduncan750@gmail.com");
        $this->command->line("  Password:karenjuduncan750! ");
        $this->command->line("  Role: super_admin");
        $this->command->line("  ID: {$admin->id}");
    }
}