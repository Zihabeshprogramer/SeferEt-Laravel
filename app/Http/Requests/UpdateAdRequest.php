<?php

namespace App\Http\Requests;

use App\Models\Ad;
use App\Services\AdImageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdRequest extends FormRequest
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
            'title' => [
                'sometimes',
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
                'sometimes',
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
            'cta_position_x' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'cta_position_y' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'cta_style' => [
                'nullable',
                'string',
                'in:primary,secondary,success,danger,warning,info',
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
            'is_active' => [
                'nullable',
                'boolean',
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
            'title.required' => 'Ad title is required.',
            'title.min' => 'Ad title must be at least 3 characters.',
            'title.max' => 'Ad title must not exceed 255 characters.',
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
    }
}
