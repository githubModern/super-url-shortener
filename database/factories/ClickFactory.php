<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Click>
 */
class ClickFactory extends Factory
{
    protected $model = Click::class;

    public function definition(): array
    {
        $referrer = $this->faker->optional(0.7)->url();
        return [
            'ip_hash' => hash('sha256', $this->faker->ipv4()),
            'country_code' => $this->faker->randomElement(['US', 'UK', 'DE', 'FR', 'CA', 'AU', 'JP', 'IN']),
            'device_type' => $this->faker->randomElement(['Mobile', 'Desktop', 'Tablet']),
            'browser' => $this->faker->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge', 'Opera']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'iOS', 'Android', 'Linux']),
            'referrer' => $referrer,
            'referrer_domain' => $referrer ? parse_url($referrer, PHP_URL_HOST) : null,
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Configure the factory to create a click for a specific link.
     * Usage: Click::factory()->forLink($link)->create()
     */
    public function forLink(Link $link): static
    {
        return $this->for($link);
    }

    public function fromCountry(string $countryCode): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => $countryCode,
        ]);
    }

    public function onDevice(?string $deviceType): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => $deviceType,
        ]);
    }

    public function withBrowser(string $browser): static
    {
        return $this->state(fn (array $attributes) => [
            'browser' => $browser,
        ]);
    }

    public function withIpHash(string $hash): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_hash' => $hash,
        ]);
    }

    public function withReferrer(?string $referrer): static
    {
        return $this->state(fn (array $attributes) => [
            'referrer' => $referrer,
            'referrer_domain' => $referrer ? parse_url($referrer, PHP_URL_HOST) : null,
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }

    public function daysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays($days),
        ]);
    }
}
