<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:taxes',
            'rate' => 'required|numeric|min:0|max:99.9999',
            'country' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}