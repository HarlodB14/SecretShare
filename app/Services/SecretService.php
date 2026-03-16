<?php

namespace App\Services;

use App\Contracts\SecretServiceInterface;
use App\Jobs\ExpireSecretJob;
use App\Models\Secret;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecretService implements SecretServiceInterface
{

    public function create(string $password, ?int $maxViews = null, ?string $expiresAt = null): Secret
    {
        $expiry = $expiresAt ? Carbon::parse($expiresAt) : null;

        $secret = Secret::create([
            'token' => Str::random(64),
            'secret' => Crypt::encryptString($password),
            'max_views' => $maxViews,
            'expires_at' => $expiry,
        ]);

        if ($expiry) {
            ExpireSecretJob::dispatch($secret->id)->delay($expiry);
        }

        return $secret;
    }

    public function reveal(Secret $secret): string
    {
        return DB::transaction(function () use ($secret): string {
            $password = Crypt::decryptString($secret->secret);

            $secret->increment('views');
            $secret->refresh();

            if ($secret->max_views === null || $secret->views >= $secret->max_views) {
                $secret->delete();
            }

            return $password;
        });
    }

    public function revoke(Secret $secret): void
    {
        $secret->delete();
    }
}

