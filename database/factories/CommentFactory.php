<?php

namespace Database\Factories;

use App\Models\Mogou;
use App\Models\SubMogou;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'content' => $this->faker->paragraph(4),
            'image_path' => null,
            'mogou_id' => Mogou::inRandomOrder()->first()->id,
            'sub_mogou_id' => SubMogou::inRandomOrder()->first()->id,
            'parent_comment_id' => null,
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
