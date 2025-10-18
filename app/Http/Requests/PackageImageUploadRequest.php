<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageImageUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('travel_agent');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB in kilobytes
                'dimensions:min_width=400,min_height=300,max_width=4000,max_height=4000'
            ],
            'draft_id' => 'nullable|string'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Please select an image to upload.',
            'image.image' => 'The uploaded file must be a valid image.',
            'image.mimes' => 'Only JPEG, PNG, JPG, and WebP images are allowed.',
            'image.max' => 'The image size must not exceed 5MB.',
            'image.dimensions' => 'The image dimensions must be at least 400x300 pixels and not exceed 4000x4000 pixels.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'image' => 'image file',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->wantsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'message' => $validator->errors()->first()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}