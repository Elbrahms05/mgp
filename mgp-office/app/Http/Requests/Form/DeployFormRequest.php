<?php

declare(strict_types=1);

namespace App\Http\Requests\Form;

use Illuminate\Foundation\Http\FormRequest;

class DeployFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('forms.manage');
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:20', 'regex:/^\d+\.\d+(\.\d+)?$/'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'version' => 'numéro de version',
            'notes'   => 'notes de version',
        ];
    }

    public function messages(): array
    {
        return [
            'version.regex' => 'Le numéro de version doit être au format X.Y ou X.Y.Z (ex: 2.1, 1.0.3).',
        ];
    }
}
