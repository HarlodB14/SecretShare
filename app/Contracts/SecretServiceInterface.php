<?php

namespace App\Contracts;

use App\Models\Secret;

interface SecretServiceInterface
{
    public function create(string $password, ?int $maxViews = null, ?string $expiresAt = null): Secret;

    public function reveal(Secret $secret): string;

    public function revoke(Secret $secret): void;
}

