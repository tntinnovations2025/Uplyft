<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GlobalAdminSeeder extends Seeder
{
    /**
     * Seed the initial Global Admin platform credential securely.
     */
    public function run(): void
    {
        $name = config('app.global_admin_name', env('GLOBAL_ADMIN_NAME', 'Global Administrator'));
        $email = strtolower(trim(config('app.global_admin_email', env('GLOBAL_ADMIN_EMAIL', 'admin@uplyft.com'))));
        $identifier = trim(config('app.global_admin_username', env('GLOBAL_ADMIN_USERNAME', 'admin')));
        $rawPassword = env('GLOBAL_ADMIN_PASSWORD', 'Admin123!@#');

        // Locate existing global admin record by email or by global_admin role
        $admin = User::withoutGlobalScopes()
            ->withTrashed()
            ->where('email', $email)
            ->orWhere('role', User::ROLE_GLOBAL_ADMIN)
            ->first();

        if ($admin) {
            if ($admin->trashed()) {
                $admin->restore();
            }

            $admin->update([
                'name'                 => $name,
                'email'                => $email,
                'identifier'           => $identifier,
                'role'                 => User::ROLE_GLOBAL_ADMIN,
                'password'             => Hash::make($rawPassword),
                'email_verified_at'    => $admin->email_verified_at ?? now(),
                'institute_id'         => null,
                'organization_id'      => null,
                'current_institute_id' => null,
                'is_delegated_admin'   => false,
                'is_primary_principal' => false,
            ]);
        } else {
            $admin = User::create([
                'name'                 => $name,
                'email'                => $email,
                'identifier'           => $identifier,
                'role'                 => User::ROLE_GLOBAL_ADMIN,
                'password'             => Hash::make($rawPassword),
                'email_verified_at'    => now(),
                'institute_id'         => null,
                'organization_id'      => null,
                'current_institute_id' => null,
                'is_delegated_admin'   => false,
                'is_primary_principal' => false,
            ]);
        }

        if (isset($this->command)) {
            $this->command->info("──────────────────────────────────────────────────────────");
            $this->command->info("  Global SaaS Admin Provisioned Successfully!             ");
            $this->command->info("──────────────────────────────────────────────────────────");
            $this->command->line("  Portal URL:  <comment>" . url('/globaladmin/login') . "</comment>");
            $this->command->line("  Email:       <info>{$email}</info>");
            $this->command->line("  Username:    <info>{$identifier}</info>");
            $this->command->line("  Password:    <info>{$rawPassword}</info>");
            $this->command->line("  Role:        <comment>" . User::ROLE_GLOBAL_ADMIN . "</comment>");
            $this->command->info("──────────────────────────────────────────────────────────");
        }
    }
}
