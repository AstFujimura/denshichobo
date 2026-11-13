<?php

namespace App\Console\Commands;

use App\Models\Control\PlatformAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformAdminCreateCommand extends Command
{
    protected $signature = 'platform:admin
                            {--name= : Login name}
                            {--password= : Plain password (random if omitted)}
                            {--display-name= : Display name}
                            {--email= : Email}
                            {--can-provision : Allow tenant provision}
                            {--can-impersonate : Allow user impersonation}
                            {--force : Update existing admin}';

    protected $description = 'Create or update a platform admin account';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Login name');
        if (! $name) {
            $this->error('name is required');

            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::random(16);
        $existing = PlatformAdmin::query()->where('name', $name)->first();

        if ($existing && ! $this->option('force')) {
            $this->error("Admin [{$name}] already exists. Use --force to update.");

            return self::FAILURE;
        }

        $payload = [
            'display_name' => $this->option('display-name') ?: $name,
            'email' => $this->option('email'),
            'password' => Hash::make($password),
            'is_active' => true,
            'can_provision' => (bool) $this->option('can-provision'),
            'can_impersonate' => (bool) $this->option('can-impersonate'),
        ];

        if ($existing) {
            $existing->fill($payload)->save();
            $admin = $existing;
            $this->info("Updated platform admin [{$name}].");
        } else {
            $admin = PlatformAdmin::query()->create(array_merge(['name' => $name], $payload));
            $this->info("Created platform admin [{$name}].");
        }

        $this->table(
            ['id', 'name', 'can_provision', 'can_impersonate', 'password'],
            [[$admin->id, $admin->name, $admin->can_provision ? 'yes' : 'no', $admin->can_impersonate ? 'yes' : 'no', $password]]
        );

        return self::SUCCESS;
    }
}
