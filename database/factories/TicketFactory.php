<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\odel=Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['VIP', 'Standard', 'Economy']),
            'price' => fake()->randomFloat(2, 20, 500),
            'quantity' => fake()->numberBetween(1, 100),
            'event_id' => \App\Models\Event::factory(),
        ];
    }
}
