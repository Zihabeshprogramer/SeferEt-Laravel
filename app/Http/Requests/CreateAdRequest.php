<?php

namespace App\Http\Requests;

use App\Models\Ad;
use Illuminate\Foundation\Http\FormRequest;

class CreateAdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isB2BUser();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'min:5'],
            'description' => ['nullable', 'string', 'max:1000'],
            
            // Image upload
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            
            // Product polymorphic (optional)
            'product_id' => ['nullable', 'integer', 'min:1'],
            'product_type' => ['nullable', 'string', 'in:' . implode(',', Ad::PRODUCT_TYPES)],
            
            // CTA fields
            'cta_text' => ['nullable', 'string', 'max:100', 'min:2'],
            'cta_action' => ['nullable', 'string', 'max:500'],
            'cta_position' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'cta_position_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cta_position_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cta_style' => ['nullable', 'string', 'in:primary,secondary,success,danger,warning,info'],
            
            // Scheduling (optional for creation)
            'start_at' => ['nullable', 'date', 'after_or_equal:today'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            
            // Priority (default 0)
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
            
            // Analytics metadata
            'analytics_meta' => ['nullable', 'array'],
            'analytics_meta.utm_source' => ['nullable', 'string', 'max:100'],
            'analytics_meta.utm_medium' => ['nullable', 'string', 'max:100'],
            'analytics_meta.utm_campaign' => ['nullable', 'string', 'max:100'],
            'analytics_meta.tracking_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Ad title is required.',
            'title.min' => 'Ad title must be at least 5 characters.',
            'title.max' => 'Ad title must not exceed 255 characters.',
            
            'image.required' => 'Banner image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, or JPG file.',
            'image.max' => 'Image size must not exceed 2MB.',
            
            'product_type.in' => 'Invalid product type selected.',
            
            'cta_text.max' => 'CTA text must not exceed 100 characters.',
            'cta_action.max' => 'CTA action must not exceed 500 characters.',
            'cta_position.min' => 'CTA position must be between 0 and 1.',
            'cta_position.max' => 'CTA position must be between 0 and 1.',
            
            'start_at.after_or_equal' => 'Start date must be today or in the future.',
            'end_at.after' => 'End date must be after the start date.',
            
            'priority.min' => 'Priority must be at least 0.',
            'priority.max' => 'Priority must not exceed 100.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'cta_position' => $this->input('cta_position', 0.5),
            'cta_style' => $this->input('cta_style', 'primary'),
            'priority' => $this->input('priority', 0),
        ]);
    }

    /**
     * Get validated data with owner information
     */
    public function validatedWithOwner(): array
    {
        $validated = $this->validated();
        
        $validated['owner_id'] = auth()->id();
        $validated['owner_type'] = get_class(auth()->user());
        $validated['status'] = Ad::STATUS_DRAFT;
        
        return $validated;
    }
}
