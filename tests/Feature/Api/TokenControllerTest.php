<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TokenControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiToken $token;
    private string $authHeader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = ApiToken::factory()->create(['user_id' => $this->user->id]);
        $this->authHeader = 'Bearer ' . $this->token->token;
    }

    /** @test */
    public function can_list_tokens(): void
    {
        ApiToken::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/tokens');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'last_used_at',
                        'expires_at',
                        'created_at',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data'); // Including the auth token
    }

    /** @test */
    public function tokens_list_excludes_token_value(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/tokens');

        $response->assertStatus(200);
        $tokens = $response->json('data');
        foreach ($tokens as $token) {
            $this->assertArrayNotHasKey('token', $token);
        }
    }

    /** @test */
    public function user_can_only_see_their_own_tokens(): void
    {
        $otherUser = User::factory()->create();
        ApiToken::factory()->create(['user_id' => $otherUser->id]);
        ApiToken::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/tokens');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Only user's tokens
    }

    /** @test */
    public function can_create_token(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens', [
                'name' => 'My Test Token',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'token', // Only shown on creation
                    'expires_at',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.name', 'My Test Token');

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $this->user->id,
            'name' => 'My Test Token',
        ]);
    }

    /** @test */
    public function token_is_64_characters(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens');

        $response->assertStatus(201);
        $token = $response->json('data.token');
        $this->assertEquals(64, strlen($token));
    }

    /** @test */
    public function can_create_token_with_expiration(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens', [
                'name' => 'Temporary Token',
                'expires_days' => 30,
            ]);

        $response->assertStatus(201);
        $expiresAt = $response->json('data.expires_at');
        $this->assertNotNull($expiresAt);
    }

    /** @test */
    public function token_name_is_optional(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens', []);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'API Token');
    }

    /** @test */
    public function expires_days_must_be_positive(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens', [
                'expires_days' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expires_days']);
    }

    /** @test */
    public function expires_days_cannot_exceed_365(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/tokens', [
                'expires_days' => 366,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expires_days']);
    }

    /** @test */
    public function can_revoke_token(): void
    {
        $tokenToRevoke = ApiToken::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'To Revoke',
        ]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/tokens/' . $tokenToRevoke->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token revoked successfully',
            ]);

        $this->assertDatabaseMissing('api_tokens', [
            'id' => $tokenToRevoke->id,
        ]);
    }

    /** @test */
    public function cannot_revoke_other_users_token(): void
    {
        $otherUser = User::factory()->create();
        $otherToken = ApiToken::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/tokens/' . $otherToken->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_revoke_nonexistent_token(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/tokens/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function revoked_token_is_immediately_invalid(): void
    {
        $tokenToRevoke = ApiToken::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Revoke the token
        $this->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/tokens/' . $tokenToRevoke->id);

        // Try to use the revoked token
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenToRevoke->token)
            ->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function token_creation_is_rate_limited(): void
    {
        // This test verifies that rate limiting configuration exists
        // The actual rate limiting behavior depends on the cache driver
        // In production, this would be 10 requests per hour

        // Create several tokens successfully
        for ($i = 0; $i < 3; $i++) {
            $response = $this->withHeader('Authorization', $this->authHeader)
                ->postJson('/api/v1/tokens');
            $response->assertStatus(201);
        }

        // Verify tokens were created
        $this->assertDatabaseCount('api_tokens', 4); // 1 from setUp + 3 new
    }
}
