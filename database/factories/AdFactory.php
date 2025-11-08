<?php

namespace Database\Factories;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $placements = ['home_banner', 'product_list', 'product_detail', 'category_page', 'search_results'];
        $deviceTypes = ['all', 'mobile', 'tablet', 'desktop'];
        $ctaStyles = ['primary', 'secondary', 'success', 'info', 'warning', 'danger'];

        return [
            'owner_id' => User::factory(),
            'owner_type' => User::class,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(2),
            'image_path' => $this->faker->optional(0.8)->imageUrl(1200, 600, 'business', true, 'Ad'),
            'cta_text' => $this->faker->optional(0.7)->randomElement([
                'Shop Now',
                'Learn More',
                'Get Started',
                'View Details',
                'Order Now',
                'See Offers',
            ]),
            'cta_action' => $this->faker->optional(0.7)->url(),
            'cta_style' => $this->faker->randomElement($ctaStyles),
            'placement' => $this->faker->randomElement($placements),
            'device_type' => $this->faker->randomElement($deviceTypes),
            'priority' => $this->faker->numberBetween(1, 10),
            'status' => 'pending',
            'is_active' => true,
            'start_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', '+1 week'),
            'end_at' => $this->faker->optional(0.2)->dateTimeBetween('+1 week', '+3 months'),
            'impressions_count' => 0,
            'clicks_count' => 0,
            'is_local_owner' => $this->faker->boolean(20),
            'admin_notes' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the ad is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the ad is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the ad is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'is_active' => true,
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_by' => User::where('role', 'admin')->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the ad is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $this->faker->randomElement([
                'Image quality does not meet our standards',
                'Content violates our advertising policies',
                'Misleading or false information',
                'Inappropriate content',
                'Product not available in marketplace',
            ]),
        ]);
    }

    /**
     * Indicate that the ad is active and running.
     */
    public function active(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the ad is inactive.
     */
    public function inactive(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the ad has high performance.
     */
    public function highPerformance(): static
    {
        return $this->active()->state(fn (array $attributes) => [
            'impressions_count' => $this->faker->numberBetween(1000, 10000),
            'clicks_count' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the ad is a home banner.
     */
    public function homeBanner(): static
    {
        return $this->state(fn (array $attributes) => [
            'placement' => 'home_banner',
            'priority' => $this->faker->numberBetween(7, 10),
        ]);
    }

    /**
     * Indicate that the ad is for mobile devices.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
        ]);
    }

    /**
     * Indicate that the ad is for desktop devices.
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
        ]);
    }

    /**
     * Indicate that the ad is from a local owner.
     */
    public function localOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_local_owner' => true,
            'priority' => $this->faker->numberBetween(5, 10),
        ]);
    }

    /**
     * Indicate that the ad is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'end_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }

    /**
     * Indicate that the ad is expired.
     */
    public function expired(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'start_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'end_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'is_active' => false,
        ]);
    }
}
