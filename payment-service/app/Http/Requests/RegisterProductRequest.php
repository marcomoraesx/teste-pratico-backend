<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProductRequest extends FormRequest
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
            'bar_code' => 'required|numeric|digits:13|unique:products,bar_code',
            'name' => 'required|string|max:127|min:3',
            'description' => 'required|string|max:255|min:3',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric',
        ];
    }
}
