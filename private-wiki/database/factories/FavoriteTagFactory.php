<?php

namespace Database\Factories;

use App\Models\FavoriteTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteTagFactory extends Factory
{
    protected $model = FavoriteTag::class;

    public function definition(): array
    {
        return [
            'tag_id' => Tag::factory(),
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}