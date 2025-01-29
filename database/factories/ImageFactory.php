<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'path' => 'images/'.$this->faker->uuid.'.jpg', // Simulated image path
            'label' => $this->faker->sentence(3), // Random short label
        ];
    }
}
