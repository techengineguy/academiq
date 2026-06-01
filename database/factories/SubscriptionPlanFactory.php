<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Starter', 'Professional', 'Enterprise']);

        return [
            'uuid' => Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10000, 100000),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'features' => [
                'Student Management',
                'Fee Management',
                'Basic Reports',
            ],
            'max_students' => fake()->randomElement([100, 500, null]),
            'max_teachers' => fake()->randomElement([10, 50, null]),
            'max_staff' => fake()->randomElement([5, 25, null]),
            'has_hostel_management' => fake()->boolean(),
            'has_exam_management' => fake()->boolean(),
            'has_assignment_management' => fake()->boolean(),
            'has_advanced_reports' => fake()->boolean(),
            'has_api_access' => fake()->boolean(),
            'has_custom_branding' => fake()->boolean(),
            'has_priority_support' => fake()->boolean(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
