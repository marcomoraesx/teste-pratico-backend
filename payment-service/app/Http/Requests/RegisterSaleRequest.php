<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|numeric',
            'items' => 'required|array',
            'items.*.product_id' => 'required|numeric|distinct',
            'items.*.quantity' => 'required|numeric|min:0',
            'payment' => 'required|array',
            'payment.payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'payment.card_number' => 'required_if:payment.payment_method,CREDIT_CARD,DEBIT_CARD|digits:16',
            'payment.cvv' => 'required_if:payment.payment_method,CREDIT_CARD,DEBIT_CARD|digits:3',
            'discount_amount' => 'required|numeric|min:0'
        ];
    }
}
