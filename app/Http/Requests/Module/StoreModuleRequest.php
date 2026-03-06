<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'domain'                  => ['required', 'string', 'max:255'],
            'name'                    => ['required', 'string', 'max:255'],
            'objective'               => ['nullable', 'string'],
            'inputs'                  => ['nullable', 'array'],
            'data_structure'          => ['nullable', 'string'],
            'logic_rules'             => ['nullable', 'string'],
            'outputs'                 => ['nullable', 'array'],
            'responsibility'          => ['nullable', 'string'],
            'failure_scenarios'       => ['nullable', 'string'],
            'audit_trail_requirements'=> ['nullable', 'string'],
            'dependencies'            => ['nullable', 'array'],
            'version_note'            => ['nullable', 'string', 'max:255'],
        ];
    }
}