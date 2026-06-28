<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required. الاسم مطلوب.',
            'name.min' => 'The name must be at least 2 characters. يجب أن يكون الاسم 2 أحرف على الأقل.',
            'email.unique' => 'This email is already in use. هذا البريد مستخدم بالفعل.',
            'password.confirmed' => 'The password confirmation does not match. تأكيد كلمة المرور غير مطابق.',
        ];
    }
}
