<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingTierRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'tier_name' => 'required|string|max:255',
            'min_users' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1|gte:min_users',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tierId = $this->route('tier');
            $tier = \App\Models\PackagePricingTier::find($tierId);

            if (!$tier) {
                return;
            }

            // Check for overlapping ranges, excluding current tier
            $query = \App\Models\PackagePricingTier::where('package_id', $tier->package_id)
                ->where('id', '!=', $tierId)
                ->where('is_active', true);

            if ($this->min_users && $this->max_users) {
                $query->where(function ($q) {
                    $q->whereBetween('min_users', [$this->min_users, $this->max_users])
                      ->orWhereBetween('max_users', [$this->min_users, $this->max_users])
                      ->orWhere(function ($q2) {
                          $q2->where('min_users', '<=', $this->min_users)
                             ->where('max_users', '>=', $this->max_users);
                      });
                });
            } elseif ($this->min_users) {
                $query->where('max_users', '>=', $this->min_users)
                      ->orWhereNull('max_users');
            } elseif ($this->max_users) {
                $query->where('min_users', '<=', $this->max_users)
                      ->orWhereNull('min_users');
            }

            if ($query->exists()) {
                $validator->errors()->add('range', 'The user range overlaps with an existing pricing tier.');
            }
        });
    }
}