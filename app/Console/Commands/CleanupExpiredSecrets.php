<?php

namespace App\Console\Commands;

use App\Models\Secret;
use Illuminate\Console\Command;

class CleanupExpiredSecrets extends Command
{

    protected $signature = 'secret:prune-expired';


    protected $description = 'Delete expired secrets from the database';


    public function handle(): int
    {
        $deleted = Secret::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Deleted {$deleted} expired secret(s).");

        return self::SUCCESS;
    }
}

