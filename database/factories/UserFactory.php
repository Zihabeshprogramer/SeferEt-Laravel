<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'avatar' => null,
            'date_of_birth' => $this->faker->optional(0.5)->date('Y-m-d', '-18 years'),
            'gender' => $this->faker->optional(0.6)->randomElement(['male', 'female']),
            'nationality' => $this->faker->optional(0.5)->country(),
            'address' => $this->faker->optional(0.4)->streetAddress(),
            'city' => $this->faker->optional(0.4)->city(),
            'state' => $this->faker->optional(0.4)->state(),
            'postal_code' => $this->faker->optional(0.4)->postcode(),
            'country' => $this->faker->optional(0.4)->country(),
            'preferences' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is a partner.
     */
    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PARTNER,
            'company_name' => $this->faker->company(),
            'company_registration_number' => $this->faker->numerify('REG-####-####'),
            'contact_phone' => $this->faker->phoneNumber(),
            'company_description' => $this->faker->optional(0.6)->paragraph(),
            'business_license' => $this->faker->optional(0.5)->numerify('LIC-####-####'),
            'tax_number' => $this->faker->optional(0.5)->numerify('TAX-####-####'),
            'website' => $this->faker->optional(0.4)->url(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
        ]);
    }

    /**
     * Indicate that the user is a travel agent.
     */
    public function travelAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_TRAVEL_AGENT,
            'company_name' => $this->faker->company() . ' Travel Agency',
            'company_registration_number' => $this->faker->numerify('REG-####-####'),
            'contact_phone' => $this->faker->phoneNumber(),
            'company_description' => $this->faker->optional(0.6)->paragraph(),
            'business_license' => $this->faker->optional(0.5)->numerify('LIC-####-####'),
            'tax_number' => $this->faker->optional(0.5)->numerify('TAX-####-####'),
            'website' => $this->faker->optional(0.4)->url(),
        ]);
    }

    /**
     * Indicate that the user is a customer.
     */
    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_CUSTOMER,
            'has_umrah_experience' => $this->faker->boolean(30),
            'completed_umrah_count' => $this->faker->optional(0.3)->numberBetween(0, 5),
            'special_requirements' => $this->faker->optional(0.2)->sentence(),
            'emergency_contact_name' => $this->faker->optional(0.5)->name(),
            'emergency_contact_phone' => $this->faker->optional(0.5)->phoneNumber(),
        ]);
    }

    /**
     * Indicate that the user is a hotel provider.
     */
    public function hotelProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_HOTEL_PROVIDER,
            'company_name' => $this->faker->company() . ' Hotels',
            'company_registration_number' => $this->faker->numerify('REG-####-####'),
            'contact_phone' => $this->faker->phoneNumber(),
            'service_type' => 'hotel',
            'service_categories' => ['budget', 'standard', 'luxury'][$this->faker->numberBetween(0, 2)],
            'coverage_areas' => ['Mecca', 'Medina', 'Jeddah'],
            'certification_number' => $this->faker->numerify('CERT-####-####'),
            'commission_rate' => $this->faker->randomFloat(2, 5, 20),
            'is_api_enabled' => $this->faker->boolean(50),
        ]);
    }

    /**
     * Indicate that the user is a transport provider.
     */
    public function transportProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_TRANSPORT_PROVIDER,
            'company_name' => $this->faker->company() . ' Transport',
            'company_registration_number' => $this->faker->numerify('REG-####-####'),
            'contact_phone' => $this->faker->phoneNumber(),
            'service_type' => 'transport',
            'service_categories' => $this->faker->randomElement(['bus', 'van', 'luxury_car']),
            'coverage_areas' => ['Mecca', 'Medina', 'Jeddah', 'Airport'],
            'certification_number' => $this->faker->numerify('CERT-####-####'),
            'commission_rate' => $this->faker->randomFloat(2, 10, 25),
            'is_api_enabled' => $this->faker->boolean(50),
        ]);
    }

    /**
     * Indicate that the user is a B2B user (partner or travel agent).
     */
    public function b2b(): static
    {
        return $this->partner();
    }

    /**
     * Indicate that the user's status is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the user's status is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_SUSPENDED,
            'suspend_reason' => $this->faker->randomElement([
                'Violation of terms of service',
                'Fraudulent activity detected',
                'Multiple complaints received',
                'Payment issues',
            ]),
        ]);
    }

    /**
     * Indicate that the user's status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_PENDING,
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
