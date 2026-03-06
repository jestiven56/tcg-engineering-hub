<?php

namespace App\Http\Requests\Artifact;

use App\Enums\ArtifactStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateArtifactStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ArtifactStatus::class)],
        ];
    }
}