<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email is required. البريد الإلكتروني مطلوب.',
            'password.required' => 'The password is required. كلمة المرور مطلوبة.',
        ];
    }
}
