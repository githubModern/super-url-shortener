<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiToken $token;
    private string $authHeader;
    private Link $link;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = ApiToken::factory()->create(['user_id' => $this->user->id]);
        $this->authHeader = 'Bearer ' . $this->token->token;
        $this->link = Link::factory()->create([
            'user_id' => $this->user->id,
            'clicks_count' => 100,
        ]);
    }

    /** @test */
    public function can_get_analytics_for_link(): void
    {
        Click::factory()->count(5)->forLink($this->link)->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'link_id',
                    'period',
                    'summary' => [
                        'total_clicks',
                        'unique_visitors',
                        'ctr',
                    ],
                    'geography',
                    'referrers',
                    'devices',
                    'browsers',
                    'daily_clicks',
                ],
            ]);
    }

    /** @test */
    public function analytics_can_be_accessed_by_short_code(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->short_code . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.link_id', $this->link->id);
    }

    /** @test */
    public function nonexistent_link_returns_404(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/nonexistent/analytics');

        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_access_analytics_for_other_users_link(): void
    {
        $otherUser = User::factory()->create();
        $otherLink = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $otherLink->id . '/analytics');

        $response->assertStatus(404);
    }

    /** @test */
    public function summary_includes_correct_click_counts(): void
    {
        // Create 10 clicks
        Click::factory()->count(10)->forLink($this->link)->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_clicks', 10);
    }

    /** @test */
    public function unique_visitors_is_calculated_correctly(): void
    {
        // Create clicks directly without factory to ensure exact control
        Click::create([
            'link_id' => $this->link->id,
            'ip_hash' => 'hash1',
            'country_code' => 'US',
            'device_type' => 'Desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'referrer' => null,
            'referrer_domain' => null,
        ]);
        
        Click::create([
            'link_id' => $this->link->id,
            'ip_hash' => 'hash1', // Same visitor
            'country_code' => 'US',
            'device_type' => 'Desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'referrer' => null,
            'referrer_domain' => null,
        ]);
        
        Click::create([
            'link_id' => $this->link->id,
            'ip_hash' => 'hash2', // Different visitor
            'country_code' => 'US',
            'device_type' => 'Mobile',
            'browser' => 'Safari',
            'os' => 'iOS',
            'referrer' => null,
            'referrer_domain' => null,
        ]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_clicks', 3)
            ->assertJsonPath('data.summary.unique_visitors', 2);
    }

    /** @test */
    public function period_filter_works_for_today(): void
    {
        // Old click
        Click::factory()
            ->forLink($this->link)
            ->daysAgo(5)
            ->create();
        // Today's click
        Click::factory()
            ->forLink($this->link)
            ->today()
            ->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics?period=today');

        $response->assertStatus(200)
            ->assertJsonPath('data.period', 'today')
            ->assertJsonPath('data.summary.total_clicks', 1);
    }

    /** @test */
    public function period_filter_works_for_week(): void
    {
        // Old click (outside week)
        Click::factory()
            ->forLink($this->link)
            ->daysAgo(10)
            ->create();
        // Recent click (within week)
        Click::factory()
            ->forLink($this->link)
            ->daysAgo(3)
            ->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics?period=week');

        $response->assertStatus(200)
            ->assertJsonPath('data.period', 'week')
            ->assertJsonPath('data.summary.total_clicks', 1);
    }

    /** @test */
    public function period_filter_works_for_month(): void
    {
        // Old click (outside month)
        Click::factory()
            ->forLink($this->link)
            ->daysAgo(35)
            ->create();
        // Recent click (within month)
        Click::factory()
            ->forLink($this->link)
            ->daysAgo(15)
            ->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics?period=month');

        $response->assertStatus(200)
            ->assertJsonPath('data.period', 'month')
            ->assertJsonPath('data.summary.total_clicks', 1);
    }

    /** @test */
    public function invalid_period_returns_validation_error(): void
    {
        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics?period=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['period']);
    }

    /** @test */
    public function geography_is_grouped_by_country(): void
    {
        Click::factory()->forLink($this->link)->fromCountry('US')->create();
        Click::factory()->forLink($this->link)->fromCountry('US')->create();
        Click::factory()->forLink($this->link)->fromCountry('UK')->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.geography.US', 2)
            ->assertJsonPath('data.geography.UK', 1);
    }

    /** @test */
    public function referrers_show_direct_for_null(): void
    {
        // Create click with no referrer (direct traffic) - directly in DB
        Click::create([
            'link_id' => $this->link->id,
            'ip_hash' => 'hash1',
            'country_code' => 'US',
            'device_type' => 'Desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'referrer' => null,
            'referrer_domain' => null,
        ]);
        
        // Create click with Google referrer - use exact domain
        Click::create([
            'link_id' => $this->link->id,
            'ip_hash' => 'hash2',
            'country_code' => 'US',
            'device_type' => 'Mobile',
            'browser' => 'Safari',
            'os' => 'iOS',
            'referrer' => 'https://google.com/search',
            'referrer_domain' => 'google.com',
        ]);

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.referrers.direct', 1);
        
        // Check that google.com referrer exists and has count 1
        $referrers = $response->json('data.referrers');
        $this->assertArrayHasKey('google.com', $referrers);
        $this->assertEquals(1, $referrers['google.com']);
    }

    /** @test */
    public function devices_are_categorized(): void
    {
        Click::factory()->forLink($this->link)->onDevice('Mobile')->create();
        Click::factory()->forLink($this->link)->onDevice('Desktop')->create();
        Click::factory()->forLink($this->link)->onDevice('Tablet')->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200)
            ->assertJsonPath('data.devices.mobile', 1)
            ->assertJsonPath('data.devices.desktop', 1)
            ->assertJsonPath('data.devices.tablet', 1);
    }

    /** @test */
    public function browsers_are_limited_to_top_6(): void
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'Brave', 'Other'];
        foreach ($browsers as $browser) {
            Click::factory()
                ->forLink($this->link)
                ->withBrowser($browser)
                ->create();
        }

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200);
        $browsers = $response->json('data.browsers');
        $this->assertCount(6, $browsers);
    }

    /** @test */
    public function daily_clicks_returns_time_series(): void
    {
        Click::factory()->forLink($this->link)->daysAgo(2)->create();
        Click::factory()->forLink($this->link)->daysAgo(2)->create();
        Click::factory()->forLink($this->link)->daysAgo(1)->create();

        $response = $this->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $this->link->id . '/analytics');

        $response->assertStatus(200);
        $dailyClicks = $response->json('data.daily_clicks');
        $this->assertGreaterThan(0, count($dailyClicks));
    }
}
