<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_category_id.required' => 'The product category field is required',
            'product_category_id.exists' => 'The selected product category is invalid',
            'name.required' => 'The name field is required',
            'price.required' => 'The price field is required',
            'stock.required' => 'The stock field is required',
        ];
    }
}
