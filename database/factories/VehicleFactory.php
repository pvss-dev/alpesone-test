<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'carro',
            'brand' => $this->faker->company(),
            'model' => $this->faker->word(),
            'version' => $this->faker->bothify('?.# ???'),
            'year_model' => $this->faker->year(),
            'year_build' => $this->faker->year(),
            'optionals' => null,
            'doors' => $this->faker->randomElement([2, 4]),
            'board' => $this->faker->unique()->bothify('???-####'),
            'chassi' => $this->faker->unique()->bothify('#################'),
            'transmission' => $this->faker->randomElement(['Manual', 'Automática', 'CVT']),
            'km' => $this->faker->numberBetween(100, 150000),
            'description' => $this->faker->sentence(),
            'sold' => $this->faker->boolean(10),
            'category' => $this->faker->randomElement(['SUV', 'Hatch', 'Sedan', 'Picape']),
            'url_car' => $this->faker->slug(),
            'price' => $this->faker->randomFloat(2, 50000, 250000),
            'color' => $this->faker->safeColorName(),
            'fuel' => $this->faker->randomElement(['Flex', 'Gasolina', 'Diesel', 'Elétrico']),
            'photos' => [
                $this->faker->imageUrl(),
                $this->faker->imageUrl()
            ]
        ];
    }
}
