<?php

declare(strict_types=1);

namespace App\Http\Requests\Form;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('forms.manage');
    }

    public function rules(): array
    {
        return [
            'form_id'   => ['required', 'string', 'max:100', 'unique:forms,form_id'],
            'form_type' => ['required', 'string', 'in:F01,F02,F03,F04,F05,F06,F07'],
            'name'      => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'form_id'   => 'identifiant formulaire ODK',
            'form_type' => 'type MGP (F01–F07)',
            'name'      => 'nom du formulaire',
            'description' => 'description',
        ];
    }
}
