<?php

namespace Tests\Feature;

use App\Jobs\ExpireSecretJob;
use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SecretExpirationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that secret is queued for expiration when created with expires_at
     */
    public function test_secret_with_expiration_dispatches_job(): void
    {
        Queue::fake();

        $expiresAt = now()->addMinutes(5)->format('Y-m-d H:i:s');

        $response = $this->post(route('secret.store'), [
            'password' => 'queued-expiry-secret',
            'expires_at' => $expiresAt,
        ]);

        $response->assertRedirect();

        $secret = Secret::query()->firstOrFail();

        Queue::assertPushed(ExpireSecretJob::class, function (ExpireSecretJob $job) use ($secret): bool {
            return $job->delay?->equalTo($secret->expires_at);
        });
    }

    /**
     * Test that expired secret is detected correctly
     */
    public function test_secret_is_marked_expired_when_past_expiration(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertTrue($secret->isExpired());
        $this->assertFalse($secret->isAccessible());
    }

    /**
     * Test that active secret is not marked as expired
     */
    public function test_secret_is_active_when_not_yet_expired(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => now()->addMinute(),
        ]);

        $this->assertFalse($secret->isExpired());
        $this->assertTrue($secret->isAccessible());
    }

    /**
     * Test that secret without expiration is always accessible
     */
    public function test_secret_without_expiration_is_always_accessible(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => null,
        ]);

        $this->assertFalse($secret->isExpired());
        $this->assertTrue($secret->isAccessible());
    }

    /**
     * Test that ExpireSecretJob deletes the secret when executed
     */
    public function test_expire_secret_job_deletes_secret(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => now()->subMinute(),
        ]);

        $secretId = $secret->id;

        // Execute the job
        $job = new ExpireSecretJob($secret->id);
        $job->handle();

        // Verify secret was deleted
        $this->assertNull(Secret::find($secretId));
    }

    /**
     * Test that accessing expired secret redirects to expired page
     */
    public function test_accessing_expired_secret_redirects_to_expired_page(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('secret.show', $secret->token));

        $response->assertRedirect(route('secret.expired'));

        $this->assertDatabaseMissing('secrets', ['id' => $secret->id]);
    }

    /**
     * Test that accessing active secret shows the secret
     */
    public function test_accessing_active_secret_shows_page(): void
    {
        $password = 'test-password';
        $secret = Secret::factory()->create([
            'expires_at' => now()->addMinute(),
            'secret' => \Illuminate\Support\Facades\Crypt::encryptString($password),
        ]);

        $response = $this->get(route('secret.show', $secret->token));

        $response->assertOk();
        $response->assertViewHas('password', $password);
    }

    /**
     * Test that refresh header is set for unexpired secrets
     */
    public function test_refresh_header_set_for_unexpired_secrets(): void
    {
        $secret = Secret::factory()->create([
            'expires_at' => now()->addSeconds(300),
            'secret' => encrypt('test-password'),
        ]);

        $response = $this->get(route('secret.show', $secret->token));

        $this->assertTrue($response->headers->has('Refresh'));
    }

    /**
     * Test that secret respects max_views limit
     */
    public function test_secret_with_max_views_limit(): void
    {
        $password = 'test-password';
        $secret = Secret::factory()->create([
            'max_views' => 1,
            'secret' => \Illuminate\Support\Facades\Crypt::encryptString($password),
        ]);

        // First view should work
        $response = $this->get(route('secret.show', $secret->token));
        $response->assertOk();

        // Secret should be deleted after first view
        $this->assertNull(Secret::find($secret->id));
    }

    /**
     * Test that secret is deleted when max_views reached
     */
    public function test_secret_deleted_when_max_views_exceeded(): void
    {
        $password = 'test-password';
        $secret = Secret::factory()->create([
            'views' => 0,
            'max_views' => 2,
            'secret' => \Illuminate\Support\Facades\Crypt::encryptString($password),
        ]);

        $secretId = $secret->id;

        // First view
        $this->get(route('secret.show', $secret->token));
        $this->assertNotNull(Secret::find($secretId));

        // Create new reference to test again
        $secret = Secret::find($secretId);
        $this->get(route('secret.show', $secret->token));

        // Secret should be deleted after max views
        $this->assertNull(Secret::find($secretId));
    }
}

