<?php

namespace App\Contracts;

use App\Models\Secret;

interface SecretServiceInterface
{
    /**
     * Create a new encrypted secret and persist it.
     *
     * @param  string       $password   The plaintext secret to encrypt and store.
     * @param  int|null     $maxViews   Maximum number of times the secret may be viewed.
     * @param  string|null  $expiresAt  ISO-8601 datetime string after which the secret expires.
     */
    public function create(string $password, ?int $maxViews = null, ?string $expiresAt = null): Secret;

    /**
     * Decrypt and reveal a secret, then enforce the deletion policy.
     * After the final allowed view the record is permanently deleted.
     *
     * @throws \RuntimeException  When the secret is no longer accessible.
     */
    public function reveal(Secret $secret): string;
}

