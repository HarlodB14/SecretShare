<?php

namespace App\Jobs;

use App\Models\Secret;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ExpireSecretJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;


    public int $timeout = 60;

    public int $tries = 3;


    public function __construct(private readonly int $secretId)
    {
        //
    }


    public function handle(): void
    {
        $secret = Secret::query()->find($this->secretId);

        // Secret may already be removed by reveal/max-views/manual revoke.
        if (!$secret) {
            return;
        }

        // Guard against non-delayed queue drivers (e.g. sync in local env).
        if (!$secret->isExpired()) {
            return;
        }

        $tokenPreview = substr($secret->token, 0, 10) . '...';

        $secret->delete();

        Log::info(
            'Secret auto-expired and deleted',
            [
                'secret_id' => $this->secretId,
                'token' => $tokenPreview,
            ]
        );
    }


    public function failed(\Throwable $exception): void
    {
        Log::error(
            "ExpireSecretJob failed for secret ID {$this->secretId}",
            [
                'secret_id' => $this->secretId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        );
    }
}
