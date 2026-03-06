<?php

namespace App\Http\Requests\Artifact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtifactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'content_json'  => ['nullable', 'array'],
        ];
    }
}