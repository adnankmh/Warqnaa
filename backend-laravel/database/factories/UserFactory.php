<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $username = $this->faker->unique()->userName();
        return [
            'username' => $username,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('Password123!'),
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'is_banned' => false,
            'email_verified_at' => now(),
        ];
    }
}
