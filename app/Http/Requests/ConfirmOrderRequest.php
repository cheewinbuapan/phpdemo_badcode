<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_address' => [
                'required',
                'string',
                'max:1000',
                'min:10'
            ],
        ];
    }

    /**
     * Get custom messages
     */
    public function messages(): array
    {
        return [
            'shipping_address.required' => 'กรุณากรอกที่อยู่จัดส่ง',
            'shipping_address.min' => 'ที่อยู่จัดส่งต้องมีความยาวอย่างน้อย 10 ตัวอักษร',
            'shipping_address.max' => 'ที่อยู่จัดส่งต้องไม่เกิน 1000 ตัวอักษร',
        ];
    }
}
