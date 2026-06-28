<?php

namespace App\Domain\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'string', 'in:credit_card,paypal'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.exists' => 'The order does not exist. الطلب غير موجود.',
            'payment_method.in' => 'The payment method must be credit_card or paypal. طريقة الدفع يجب أن تكون credit_card أو paypal.',
        ];
    }
}
