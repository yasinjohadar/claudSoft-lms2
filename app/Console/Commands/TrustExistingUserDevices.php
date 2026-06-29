<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Console\Command;

class TrustExistingUserDevices extends Command
{
    protected $signature = 'devices:trust-existing {--user= : Trust devices for a specific user ID only}';

    protected $description = 'Mark the most recently used device as trusted for each user (migration helper)';

    public function handle(): int
    {
        $userId = $this->option('user');

        $query = User::query()->when($userId, fn ($q) => $q->where('id', $userId));

        $trustedCount = 0;

        $query->orderBy('id')->chunkById(100, function ($users) use (&$trustedCount) {
            foreach ($users as $user) {
                $device = UserDevice::query()
                    ->where('user_id', $user->id)
                    ->orderByDesc('last_used_at')
                    ->first();

                if (! $device) {
                    continue;
                }

                if (! $device->is_trusted && ! $device->is_blocked) {
                    $device->update(['is_trusted' => true]);
                    $trustedCount++;
                }
            }
        });

        $this->info("Trusted {$trustedCount} device(s).");

        return self::SUCCESS;
    }
}
