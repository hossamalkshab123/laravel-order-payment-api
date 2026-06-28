<?php

namespace App\Domain\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'min:2'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'The customer name is required. الاسم مطلوب.',
            'email.required' => 'The email is required. البريد الإلكتروني مطلوب.',
            'currency.required' => 'The currency is required. العملة مطلوبة.',
            'items.required' => 'At least one item is required. يجب إدخال عنصر واحد على الأقل.',
        ];
    }
}
