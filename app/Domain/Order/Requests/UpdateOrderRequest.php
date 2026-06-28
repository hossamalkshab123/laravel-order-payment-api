<?php

namespace App\Domain\Order\Requests;

use App\Domain\Order\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(OrderStatus::values())],
            'customer_name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_name' => ['required_with:items', 'string', 'min:2'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The status must be one of pending, confirmed, cancelled. يجب أن تكون الحالة واحدة من pending أو confirmed أو cancelled.',
            'customer_name.min' => 'The customer name must be at least 2 characters. اسم العميل يجب أن يكون 2 أحرف على الأقل.',
            'email.email' => 'The email must be a valid email address. البريد الإلكتروني يجب أن يكون صيغة صحيحة.',
            'items.*.price.min' => 'Item price must be greater than 0. سعر المنتج يجب أن يكون أكبر من 0.',
        ];
    }
}
