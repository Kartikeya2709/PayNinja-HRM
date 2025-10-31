<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:packages,name,' . $this->route('package'),
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:fixed,tiered',
            'base_price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:monthly,yearly',
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