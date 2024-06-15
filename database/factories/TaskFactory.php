<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->text(),
            'due_date' => fake()->dateTimeBetween(Carbon::now()->subDays(7), Carbon::now()->addDays(7)),
            'is_completed' => fake()->boolean(),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
        ];
    }
}
