<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:discounts|alpha_num',
            'description' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date|before:valid_until',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:packages,id',
            'is_active' => 'boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'code' => strtoupper($this->code),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->discount_type === 'percentage' && $this->discount_value > 100) {
                $validator->errors()->add('discount_value', 'Percentage discount cannot exceed 100%.');
            }
        });
    }
}