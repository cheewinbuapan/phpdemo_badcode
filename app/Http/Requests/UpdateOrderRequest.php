<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'products' => [
                'required',
                'array',
                'min:1'
            ],
            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,product_id'
            ],
            'products.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999'
            ],
        ];
    }

    /**
     * Get custom messages
     */
    public function messages(): array
    {
        return [
            'products.required' => 'กรุณาเลือกสินค้าอย่างน้อย 1 รายการ',
            'products.min' => 'กรุณาเลือกสินค้าอย่างน้อย 1 รายการ',
            'products.*.product_id.exists' => 'สินค้าที่เลือกไม่ถูกต้อง',
            'products.*.quantity.required' => 'กรุณาระบุจำนวนสินค้า',
            'products.*.quantity.min' => 'จำนวนสินค้าต้องมากกว่า 0',
        ];
    }
}
