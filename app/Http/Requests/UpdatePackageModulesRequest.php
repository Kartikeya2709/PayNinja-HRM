<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageModulesRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('superadmin');
    }

    public function rules()
    {
        return [
            'modules' => 'required|array',
            'modules.*.name' => 'required|string',
            'modules.*.has_access' => 'boolean',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->modules) {
            $modules = [];
            foreach ($this->modules as $module) {
                $modules[] = [
                    'name' => $module['name'],
                    'has_access' => isset($module['has_access']) ? (bool) $module['has_access'] : true,
                ];
            }
            $this->merge(['modules' => $modules]);
        }
    }
}