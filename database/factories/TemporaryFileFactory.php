<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TemporaryFile;

class TemporaryFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TemporaryFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sequence = $this->faker->unique()->randomNumber();

        return [
            'id' => rand(),
            'folder' => 'folder_'.$sequence,
            'filename' => 'file_'.$sequence.'.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
