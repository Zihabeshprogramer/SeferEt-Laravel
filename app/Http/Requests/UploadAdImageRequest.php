<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAdImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $ad = $this->route('ad');
        return auth()->check() && 
               auth()->user()->can('uploadImage', $ad);
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
                'max:10240', // 10MB in kilobytes
                'dimensions:min_width=800,min_height=400,max_width=4000,max_height=4000'
            ],
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
            'image.max' => 'The image size must not exceed 10MB.',
            'image.dimensions' => 'The image dimensions must be at least 800x400 pixels and not exceed 4000x4000 pixels.',
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
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
