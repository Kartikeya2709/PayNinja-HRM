<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignPackageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'package_id' => 'required|exists:packages,id',
        ];
    }
}