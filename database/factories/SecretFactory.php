<?php

namespace Database\Factories;

use App\Models\Secret;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @extends Factory<Secret>
 */
class SecretFactory extends Factory
{
    protected $model = Secret::class;

    public function definition(): array
    {
        return [
            'token'      => Str::random(64),
            'secret'     => Crypt::encryptString($this->faker->sentence()),
            'views'      => 0,
            'max_views'  => null,
            'expires_at' => null,
        ];
    }
}

