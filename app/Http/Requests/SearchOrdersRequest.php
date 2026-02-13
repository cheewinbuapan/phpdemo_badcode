<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9ก-๙\s\-@.]+$/' // Allow alphanumeric, Thai, spaces, hyphens, @, .
            ],
        ];
    }

    /**
     * Get custom messages
     */
    public function messages(): array
    {
        return [
            'search.regex' => 'คำค้นหาประกอบด้วยอักษร ตัวเลข และช่องว่างเท่านั้น',
            'search.max' => 'คำค้นหาต้องไม่เกิน 100 ตัวอักษร',
        ];
    }
}
