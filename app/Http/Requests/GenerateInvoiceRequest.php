<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'company_package_id' => 'required|exists:company_packages,id',
            'user_count' => 'nullable|integer|min:1',
            'discount_id' => 'nullable|exists:discounts,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'billing_period_start' => 'nullable|date|before:billing_period_end',
            'billing_period_end' => 'nullable|date|after:billing_period_start',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->company_package_id) {
                $companyPackage = \App\Models\CompanyPackage::with('package')->find($this->company_package_id);

                if ($companyPackage && !$companyPackage->is_active) {
                    $validator->errors()->add('company_package_id', 'Cannot generate invoice for inactive package assignment.');
                }
            }

            if ($this->discount_id) {
                $discount = \App\Models\Discount::find($this->discount_id);
                if ($discount && !$discount->canBeUsed()) {
                    $validator->errors()->add('discount_id', 'Selected discount is not valid or has expired.');
                }
            }
        });
    }
}