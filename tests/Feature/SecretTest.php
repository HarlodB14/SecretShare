<?php

namespace Tests\Feature;

use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SecretTest extends TestCase
{
    use RefreshDatabase;

    // Home / create form

    public function test_home_page_is_accessible(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertViewIs('secret.create');
    }

    public function test_secret_create_route_is_accessible_via_get(): void
    {
        $this->get(route('secret.create'))
            ->assertOk()
            ->assertViewIs('secret.create');
    }

    // Storing a secret

    public function test_user_can_create_a_secret(): void
    {
        $response = $this->post(route('secret.store'), [
            'password' => 'super-secret-password',
        ]);

        $this->assertDatabaseCount('secrets', 1);

        $secret = Secret::first();

        $response->assertRedirect(route('secret.created', $secret->token));

        $this->get(route('secret.created', $secret->token))
            ->assertOk()
            ->assertViewIs('secret.created')
            ->assertViewHas('link', route('secret.show', $secret->token));
    }

    public function test_secret_is_encrypted_at_rest(): void
    {
        $this->post(route('secret.store'), ['password' => 'plaintext-value']);

        $stored = Secret::first();

        // The raw DB value must never equal the plaintext.
        $this->assertNotEquals('plaintext-value', $stored->secret);

        // But decrypting it must yield the original value.
        $this->assertEquals('plaintext-value', Crypt::decryptString($stored->secret));
    }

    public function test_token_is_64_characters(): void
    {
        $this->post(route('secret.store'), ['password' => 'test']);

        $this->assertEquals(64, strlen(Secret::first()->token));
    }

    public function test_two_secrets_receive_unique_tokens(): void
    {
        $this->post(route('secret.store'), ['password' => 'first']);
        $this->post(route('secret.store'), ['password' => 'second']);

        $tokens = Secret::pluck('token')->toArray();
        $this->assertCount(2, array_unique($tokens));
    }

    public function test_password_is_required(): void
    {
        $this->post(route('secret.store'), ['password' => ''])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('secrets', 0);
    }

    public function test_max_views_must_be_a_positive_integer(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'max_views' => 0,
        ])->assertSessionHasErrors('max_views');
    }

    public function test_expires_at_must_be_in_the_future(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'expires_at' => now()->subHour()->toDateTimeString(),
        ])->assertSessionHasErrors('expires_at');
    }

    public function test_secret_can_be_created_with_optional_fields(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'max_views' => 3,
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $secret = Secret::first();
        $this->assertEquals(3, $secret->max_views);
        $this->assertNotNull($secret->expires_at);
    }

    // viewing a secret (one-time link behaviour)

    public function test_secret_is_decrypted_and_shown_on_first_view(): void
    {
        $this->post(route('secret.store'), ['password' => 'my-password']);
        $token = Secret::first()->token;

        $this->get(route('secret.show', $token))
            ->assertOk()
            ->assertViewIs('secret.show')
            ->assertViewHas('password', 'my-password');
    }

    public function test_revealed_secret_response_is_not_cacheable(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'cache-sensitive',
            'max_views' => 2,
            'expires_at' => now()->addMinutes(10)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->get(route('secret.show', Secret::first()->token));
        $cacheControl = (string)$response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
    }

    public function test_secret_is_deleted_after_single_view_by_default(): void
    {
        $this->post(route('secret.store'), ['password' => 'test']);
        $token = Secret::first()->token;

        $this->get(route('secret.show', $token));

        $this->assertDatabaseCount('secrets', 0);
    }

    public function test_second_view_of_one_time_secret_returns_404(): void
    {
        $this->post(route('secret.store'), ['password' => 'test']);
        $token = Secret::first()->token;

        $this->get(route('secret.show', $token));               // first view — consumes it
        $this->get(route('secret.show', $token))->assertNotFound(); // deleted → 404
    }

    // max-views behaviour

    public function test_secret_survives_until_max_views_is_reached(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'multi-view',
            'max_views' => 3,
        ]);
        $token = Secret::first()->token;

        $this->get(route('secret.show', $token))->assertOk();
        $this->assertDatabaseCount('secrets', 1); // still alive after view 1

        $this->get(route('secret.show', $token))->assertOk();
        $this->assertDatabaseCount('secrets', 1); // still alive after view 2

        $this->get(route('secret.show', $token))->assertOk();
        $this->assertDatabaseCount('secrets', 0); // deleted after view 3
    }

    public function test_secret_with_max_views_is_gone_after_limit(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'max_views' => 2,
        ]);
        $token = Secret::first()->token;

        $this->get(route('secret.show', $token));
        $this->get(route('secret.show', $token));

        $this->get(route('secret.show', $token))->assertNotFound();
    }

    // Expiry behaviour

    public function test_expired_secret_returns_expired_view(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'expires_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

        // Manually wind the clock past expiry.
        Secret::first()->update(['expires_at' => now()->subMinute()]);

        $this->followingRedirects()
            ->get(route('secret.show', Secret::first()->token))
            ->assertOk()
            ->assertViewIs('secret.expired');
    }

    public function test_expired_page_can_be_refreshed(): void
    {
        $this->get(route('secret.expired'))
            ->assertOk()
            ->assertViewIs('secret.expired');
    }

    public function test_expiring_secret_response_has_backend_refresh_header(): void
    {
        $this->post(route('secret.store'), [
            'password' => 'test',
            'max_views' => 2,
            'expires_at' => now()->addMinute()->format('Y-m-d H:i:s'),
        ]);

        $token = Secret::first()->token;

        $response = $this->get(route('secret.show', $token));
        $refreshHeader = (string)$response->headers->get('Refresh');

        $this->assertNotSame('', $refreshHeader);
        $this->assertStringContainsString('url=' . route('secret.expired'), $refreshHeader);
    }

    public function test_expired_secret_is_deleted_from_db_on_access(): void
    {
        $this->post(route('secret.store'), ['password' => 'test', 'expires_at' => now()->addHour()->format('Y-m-d H:i:s')]);
        Secret::first()->update(['expires_at' => now()->subMinute()]);

        $this->get(route('secret.show', Secret::first()->token));

        $this->assertDatabaseCount('secrets', 0);
    }

    // Edge cases

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('secret.show', 'nonexistent-token'))->assertNotFound();
    }

    public function test_prune_expired_command_deletes_expired_secrets(): void
    {
        Secret::factory()->create(['expires_at' => now()->subMinute()]);
        Secret::factory()->create(['expires_at' => now()->addMinute()]);

        Artisan::call('secret:prune-expired');

        $this->assertDatabaseCount('secrets', 1);
    }

    // Secret model helper methods

    public function test_is_expired_returns_true_for_past_date(): void
    {
        $secret = Secret::factory()->create(['expires_at' => now()->subDay()]);
        $this->assertTrue($secret->isExpired());
    }

    public function test_is_expired_returns_false_for_future_date(): void
    {
        $secret = Secret::factory()->create(['expires_at' => now()->addDay()]);
        $this->assertFalse($secret->isExpired());
    }

    public function test_is_expired_returns_false_when_no_expiry_set(): void
    {
        $secret = Secret::factory()->create(['expires_at' => null]);
        $this->assertFalse($secret->isExpired());
    }

    public function test_has_reached_max_views_returns_true_when_limit_hit(): void
    {
        $secret = Secret::factory()->create(['max_views' => 2, 'views' => 2]);
        $this->assertTrue($secret->hasReachedMaxViews());
    }

    public function test_has_reached_max_views_returns_false_when_below_limit(): void
    {
        $secret = Secret::factory()->create(['max_views' => 5, 'views' => 2]);
        $this->assertFalse($secret->hasReachedMaxViews());
    }

    public function test_is_accessible_returns_false_when_expired(): void
    {
        $secret = Secret::factory()->create(['expires_at' => now()->subHour()]);
        $this->assertFalse($secret->isAccessible());
    }

    public function test_is_accessible_returns_false_when_max_views_reached(): void
    {
        $secret = Secret::factory()->create(['max_views' => 1, 'views' => 1]);
        $this->assertFalse($secret->isAccessible());
    }

    public function test_is_accessible_returns_true_for_fresh_secret(): void
    {
        $secret = Secret::factory()->create();
        $this->assertTrue($secret->isAccessible());
    }
}

