<?php

namespace Database\Factories;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username'           => fake()->unique()->userName(),
            'email'              => fake()->unique()->safeEmail(),
            'password'           => static::$password ??= Hash::make('password'),
            'first_name'         => fake()->firstName(),
            'last_name'          => fake()->lastName(),
            'phone'              => fake()->phoneNumber(),
            'is_active'          => true,
            'preferred_language' => 'fr',
            'email_verified_at'  => now(),
            'remember_token'     => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
