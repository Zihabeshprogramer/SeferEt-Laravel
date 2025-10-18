<?php

namespace App\Http\Requests;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('travel_agent');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic package information
            'name' => ['required', 'string', 'max:255', 'unique:packages,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:packages,slug', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['required', 'string', 'max:1000'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'detailed_description' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::in(Package::TYPES)],
            
            // Destination and categorization
            'destinations' => ['nullable', 'array', 'max:10'],
            'destinations.*' => ['string', 'max:100'],
            'categories' => ['nullable', 'array', 'max:10'],
            'categories.*' => ['string', 'in:historical,nature,beaches,mountains,cities,food,shopping,nightlife,religious,festivals'],
            'difficulty_level' => ['nullable', 'string', 'in:easy,moderate,challenging,expert'],
            
            // Duration and dates
            'duration' => ['required', 'integer', 'min:1', 'max:365'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            
            // Pricing information
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'child_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'child_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'child_price_disabled' => ['nullable', 'boolean'],
            'child_discount_percent_disabled' => ['nullable', 'boolean'],
            'infant_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'single_supplement' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'currency' => ['required', 'string', 'size:3', 'in:USD,EUR,GBP,SAR,AED,TRY'],
            'total_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'deposit_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'platform_commission' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'terms_accepted' => ['nullable', 'array'],
            
            // Pricing breakdown and add-ons
            'pricing_breakdown' => ['nullable', 'array'],
            'pricing_breakdown.*.item' => ['required_with:pricing_breakdown', 'string', 'max:255'],
            'pricing_breakdown.*.amount' => ['required_with:pricing_breakdown', 'numeric', 'min:0'],
            'pricing_breakdown.*.description' => ['nullable', 'string', 'max:500'],
            
            'optional_addons' => ['nullable', 'array'],
            'optional_addons.*.name' => ['required_with:optional_addons', 'string', 'max:255'],
            'optional_addons.*.price' => ['required_with:optional_addons', 'numeric', 'min:0'],
            'optional_addons.*.description' => ['nullable', 'string', 'max:500'],
            'optional_addons.*.is_required' => ['nullable', 'boolean'],
            
            // Payment and cancellation
            'payment_terms' => ['nullable', 'array'],
            'payment_terms.installments' => ['nullable', 'integer', 'min:1', 'max:12'],
            'payment_terms.final_payment_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'payment_terms.methods' => ['nullable', 'array'],
            
            'cancellation_policy' => ['nullable', 'array'],
            'cancellation_policy.*.days_before' => ['required_with:cancellation_policy', 'integer', 'min:0'],
            'cancellation_policy.*.penalty_percentage' => ['required_with:cancellation_policy', 'numeric', 'min:0', 'max:100'],
            
            // Inclusions
            'includes_meals' => ['nullable', 'boolean'],
            'includes_accommodation' => ['nullable', 'boolean'],
            'includes_transport' => ['nullable', 'boolean'],
            'includes_guide' => ['nullable', 'boolean'],
            'includes_flights' => ['nullable', 'boolean'],
            'includes_activities' => ['nullable', 'boolean'],
            'free_cancellation' => ['nullable', 'boolean'],
            'instant_confirmation' => ['nullable', 'boolean'],
            
            // Capacity and availability
            'min_participants' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:1000', 'gte:min_participants'],
            'min_booking_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'requires_deposit' => ['nullable', 'boolean'],
            'available_from' => ['nullable', 'date', 'after_or_equal:today'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            
            // Content arrays
            'inclusions' => ['nullable', 'array'],
            'inclusions.*' => ['string', 'max:500'],
            
            'exclusions' => ['nullable', 'array'],
            'exclusions.*' => ['string', 'max:500'],
            
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['string', 'max:255'],
            
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            
            'special_offers' => ['nullable', 'array'],
            'special_offers.*.title' => ['required_with:special_offers', 'string', 'max:255'],
            'special_offers.*.description' => ['nullable', 'string', 'max:500'],
            'special_offers.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'special_offers.*.valid_until' => ['nullable', 'date', 'after:today'],
            
            // Provider management
            'uses_b2b_services' => ['nullable', 'boolean'],
            'hotel_source' => ['required', Rule::in(Package::PROVIDER_SOURCES)],
            'transport_source' => ['required', Rule::in(Package::PROVIDER_SOURCES)],
            'flight_source' => ['required', Rule::in(Package::FLIGHT_SOURCES)],
            
            'external_providers' => ['nullable', 'array'],
            'external_providers.hotels' => ['nullable', 'array'],
            'external_providers.hotels.*.name' => ['required_with:external_providers.hotels', 'string', 'max:255'],
            'external_providers.hotels.*.contact' => ['nullable', 'string', 'max:255'],
            'external_providers.hotels.*.notes' => ['nullable', 'string', 'max:1000'],
            
            'external_providers.transport' => ['nullable', 'array'],
            'external_providers.transport.*.name' => ['required_with:external_providers.transport', 'string', 'max:255'],
            'external_providers.transport.*.contact' => ['nullable', 'string', 'max:255'],
            'external_providers.transport.*.notes' => ['nullable', 'string', 'max:1000'],
            
            // Location and logistics
            'departure_cities' => ['nullable', 'array'],
            'departure_cities.*' => ['string', 'max:100'],
            
            'meeting_points' => ['nullable', 'array'],
            'meeting_points.*.location' => ['required_with:meeting_points', 'string', 'max:255'],
            'meeting_points.*.address' => ['nullable', 'string', 'max:500'],
            'meeting_points.*.instructions' => ['nullable', 'string', 'max:1000'],
            'meeting_points.*.time' => ['nullable', 'date_format:H:i'],
            
            'pickup_locations' => ['nullable', 'array'],
            'pickup_locations.*' => ['string', 'max:255'],
            
            // Requirements and documentation
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:255'],
            
            'visa_requirements' => ['nullable', 'array'],
            'visa_requirements.required' => ['nullable', 'boolean'],
            'visa_requirements.countries' => ['nullable', 'array'],
            'visa_requirements.processing_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'visa_requirements.notes' => ['nullable', 'string', 'max:1000'],
            
            'health_requirements' => ['nullable', 'array'],
            'health_requirements.vaccinations' => ['nullable', 'array'],
            'health_requirements.medical_certificate' => ['nullable', 'boolean'],
            'health_requirements.insurance_required' => ['nullable', 'boolean'],
            'health_requirements.notes' => ['nullable', 'string', 'max:1000'],
            
            'age_restrictions' => ['nullable', 'array'],
            'age_restrictions.min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_restrictions.max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:age_restrictions.min_age'],
            'age_restrictions.child_policy' => ['nullable', 'string', 'max:500'],
            'age_restrictions.senior_policy' => ['nullable', 'string', 'max:500'],
            
            // Features and options
            'is_featured' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'allow_customization' => ['nullable', 'boolean'],
            'instant_booking' => ['nullable', 'boolean'],
            
            'customization_options' => ['nullable', 'array'],
            'customization_options.accommodation' => ['nullable', 'boolean'],
            'customization_options.activities' => ['nullable', 'boolean'],
            'customization_options.transport' => ['nullable', 'boolean'],
            'customization_options.dates' => ['nullable', 'boolean'],
            
            'group_discounts' => ['nullable', 'array'],
            'group_discounts.*.min_participants' => ['required_with:group_discounts', 'integer', 'min:2'],
            'group_discounts.*.discount_percentage' => ['required_with:group_discounts', 'numeric', 'min:0', 'max:50'],
            
            'seasonal_pricing' => ['nullable', 'array'],
            'seasonal_pricing.*.season' => ['required_with:seasonal_pricing', 'string', 'max:50'],
            'seasonal_pricing.*.start_date' => ['required_with:seasonal_pricing', 'date'],
            'seasonal_pricing.*.end_date' => ['required_with:seasonal_pricing', 'date', 'after:seasonal_pricing.*.start_date'],
            'seasonal_pricing.*.price_modifier' => ['required_with:seasonal_pricing', 'numeric', 'min:-50', 'max:100'],
            
            // Images and media
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['string', 'max:500', 'url'],
            
            // SEO and marketing
            'seo_meta' => ['nullable', 'array'],
            'seo_meta.title' => ['nullable', 'string', 'max:60'],
            'seo_meta.description' => ['nullable', 'string', 'max:160'],
            'seo_meta.keywords' => ['nullable', 'array'],
            'seo_meta.keywords.*' => ['string', 'max:50'],
            
            // Multi-language support
            'multi_language' => ['nullable', 'array'],
            'multi_language.*.lang' => ['required_with:multi_language', 'string', 'size:2'],
            'multi_language.*.name' => ['required_with:multi_language', 'string', 'max:255'],
            'multi_language.*.description' => ['required_with:multi_language', 'string', 'max:1000'],
            
            // Status
            'status' => ['required', Rule::in(Package::STATUSES)],
            'requires_approval' => ['nullable', 'boolean'],
            
            // Legacy support
            'itinerary' => ['nullable', 'array'],
            'service_preferences' => ['nullable', 'array'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Package name is required.',
            'name.unique' => 'A package with this name already exists.',
            'description.required' => 'Package description is required.',
            'type.required' => 'Package type is required.',
            'type.in' => 'Invalid package type selected.',
            'duration.required' => 'Package duration is required.',
            'duration.min' => 'Package duration must be at least 1 day.',
            'base_price.required' => 'Base price is required.',
            'base_price.min' => 'Base price must be greater than 0.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Invalid currency selected.',
            'start_date.after_or_equal' => 'Start date must be today or a future date.',
            'end_date.after' => 'End date must be after the start date.',
            'max_participants.gte' => 'Maximum participants must be greater than or equal to minimum participants.',
            'hotel_source.required' => 'Hotel source selection is required.',
            'transport_source.required' => 'Transport source selection is required.',
            'flight_source.required' => 'Flight source selection is required.',
            'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already taken.',
            'deposit_percentage.max' => 'Deposit percentage cannot exceed 100%.',
            'platform_commission.max' => 'Platform commission cannot exceed 50%.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.url' => 'Each image must be a valid URL.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation for date consistency
            if ($this->start_date && $this->end_date && $this->duration) {
                $startDate = \Carbon\Carbon::parse($this->start_date);
                $endDate = \Carbon\Carbon::parse($this->end_date);
                $calculatedDuration = $startDate->diffInDays($endDate) + 1;
                
                if ($calculatedDuration !== $this->duration) {
                    $validator->errors()->add('duration', 'Duration does not match the difference between start and end dates.');
                }
            }
            
            // Validate pricing breakdown total
            if ($this->pricing_breakdown && $this->total_price) {
                $breakdownTotal = collect($this->pricing_breakdown)->sum('amount');
                if (abs($breakdownTotal - $this->total_price) > 0.01) {
                    $validator->errors()->add('pricing_breakdown', 'Pricing breakdown total must match the total price.');
                }
            }
            
            // Validate capacity constraints
            if ($this->min_participants && $this->max_participants) {
                if ($this->min_participants > $this->max_participants) {
                    $validator->errors()->add('min_participants', 'Minimum participants cannot be greater than maximum participants.');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }
        
        // Set default values
        $this->merge([
            'uses_b2b_services' => $this->boolean('uses_b2b_services', false),
            'is_featured' => $this->boolean('is_featured', false),
            'is_premium' => $this->boolean('is_premium', false),
            'allow_customization' => $this->boolean('allow_customization', false),
            'instant_booking' => $this->boolean('instant_booking', false),
            'requires_approval' => $this->boolean('requires_approval', true),
            
            // Inclusion fields
            'includes_meals' => $this->boolean('includes_meals', false),
            'includes_accommodation' => $this->boolean('includes_accommodation', false),
            'includes_transport' => $this->boolean('includes_transport', false),
            'includes_guide' => $this->boolean('includes_guide', false),
            'includes_flights' => $this->boolean('includes_flights', false),
            'includes_activities' => $this->boolean('includes_activities', false),
            'free_cancellation' => $this->boolean('free_cancellation', false),
            'instant_confirmation' => $this->boolean('instant_confirmation', false),
            
            // Other boolean fields
            'child_price_disabled' => $this->boolean('child_price_disabled', false),
            'child_discount_percent_disabled' => $this->boolean('child_discount_percent_disabled', false),
            'requires_deposit' => $this->boolean('requires_deposit', false),
        ]);
        
        // Calculate total price if not provided
        if (!$this->total_price && $this->pricing_breakdown) {
            $total = collect($this->pricing_breakdown)->sum('amount');
            if ($total > 0) {
                $this->merge(['total_price' => $total]);
            }
        }
    }
}