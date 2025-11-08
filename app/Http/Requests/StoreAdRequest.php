<?php

namespace App\Http\Requests;

use App\Models\Ad;
use App\Models\Package;
use App\Models\Hotel;
use App\Models\Flight;
use App\Models\TransportService;
use App\Services\AdImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $productType = $this->input('product_type');
                    if (!$this->validateProductOwnership($value, $productType)) {
                        $fail('You do not own this product or it does not exist.');
                    }
                },
            ],
            'product_type' => [
                'required',
                'string',
                Rule::in(Ad::PRODUCT_TYPES),
            ],
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:' . (AdImageService::MAX_FILE_SIZE), // 5MB in KB
            ],
            'cta_text' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $imageService = app(AdImageService::class);
                        $errors = $imageService->validateCtaText($value);
                        if (!empty($errors)) {
                            $fail(implode(', ', $errors));
                        }
                    }
                },
            ],
            'cta_action' => [
                'nullable',
                'string',
                'max:500',
                'url',
            ],
            'cta_position' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],
            'cta_style' => [
                'nullable',
                'string',
                Rule::in(Ad::CTA_STYLES),
            ],
            'start_at' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'end_at' => [
                'nullable',
                'date',
                'after:start_at',
            ],
            'priority' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'analytics_meta' => [
                'nullable',
                'array',
            ],
            'analytics_meta.utm_source' => [
                'nullable',
                'string',
                'max:100',
            ],
            'analytics_meta.utm_medium' => [
                'nullable',
                'string',
                'max:100',
            ],
            'analytics_meta.utm_campaign' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product to advertise.',
            'product_type.required' => 'Product type is required.',
            'product_type.in' => 'Invalid product type selected.',
            'title.required' => 'Ad title is required.',
            'title.min' => 'Ad title must be at least 3 characters.',
            'title.max' => 'Ad title must not exceed 255 characters.',
            'image.required' => 'Please upload an image for your ad.',
            'image.mimes' => 'Image must be in JPEG, PNG, or WebP format.',
            'image.max' => 'Image size must not exceed 5MB.',
            'cta_text.max' => 'CTA text must not exceed 100 characters.',
            'cta_action.url' => 'CTA action must be a valid URL.',
            'cta_position.min' => 'CTA position must be between 0 and 1.',
            'cta_position.max' => 'CTA position must be between 0 and 1.',
            'start_at.after_or_equal' => 'Start date must be today or in the future.',
            'end_at.after' => 'End date must be after the start date.',
        ];
    }

    /**
     * Validate that the user owns the product
     */
    protected function validateProductOwnership(int $productId, ?string $productType): bool
    {
        if (!$productType) {
            return false;
        }

        $user = $this->user();
        if (!$user) {
            return false;
        }

        return match ($productType) {
            Ad::PRODUCT_TYPE_PACKAGE => Package::where('id', $productId)
                ->where('creator_id', $user->id)
                ->exists(),
            
            Ad::PRODUCT_TYPE_HOTEL => Hotel::where('id', $productId)
                ->where('provider_id', $user->id)
                ->exists(),
            
            Ad::PRODUCT_TYPE_FLIGHT => Flight::where('id', $productId)
                ->where('provider_id', $user->id)
                ->exists(),
            
            Ad::PRODUCT_TYPE_TRANSPORT => TransportService::where('id', $productId)
                ->where('provider_id', $user->id)
                ->exists(),
            
            default => false,
        };
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize CTA text if provided
        if ($this->has('cta_text') && $this->input('cta_text')) {
            $imageService = app(AdImageService::class);
            $this->merge([
                'cta_text' => $imageService->sanitizeCtaText($this->input('cta_text')),
            ]);
        }

        // Set default CTA position if not provided
        if (!$this->has('cta_position')) {
            $this->merge(['cta_position' => 0.5]);
        }

        // Set default CTA style if not provided
        if (!$this->has('cta_style')) {
            $this->merge(['cta_style' => Ad::CTA_STYLE_PRIMARY]);
        }
    }
}
