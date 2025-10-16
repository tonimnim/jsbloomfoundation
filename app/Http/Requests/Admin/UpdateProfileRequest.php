<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'county' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_picture' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048' // 2MB max
            ],
            'newsletter_subscribed' => ['boolean'],
            'email_notifications' => ['boolean'],
            'sms_notifications' => ['boolean'],
            'preferred_causes' => ['nullable', 'array'],
            'preferred_causes.*' => ['string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'profile_picture.image' => 'Profile picture must be an image file.',
            'profile_picture.mimes' => 'Profile picture must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'profile_picture.max' => 'Profile picture cannot be larger than 2MB.',
            'address.max' => 'Address cannot exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to booleans
        $this->merge([
            'newsletter_subscribed' => $this->boolean('newsletter_subscribed'),
            'email_notifications' => $this->boolean('email_notifications'),
            'sms_notifications' => $this->boolean('sms_notifications'),
        ]);

        // Handle preferred causes as array
        if ($this->has('preferred_causes') && is_string($this->preferred_causes)) {
            $this->merge([
                'preferred_causes' => json_decode($this->preferred_causes, true) ?? []
            ]);
        }
    }
}