<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiToken>
 */
class ApiTokenFactory extends Factory
{
    protected $model = ApiToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::random(64),
            'name' => $this->faker->words(2, true),
            'last_used_at' => null,
            'expires_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function usedRecently(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now(),
        ]);
    }
}
