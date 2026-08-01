<?php

declare(strict_types=1);

namespace App\Http\Requests\Mediation;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('mediation.create');
    }

    public function rules(): array
    {
        return [
            'complaint_id'         => ['required', 'exists:complaints,id'],
            'session_date'         => ['required', 'date', 'before_or_equal:today'],
            'presided_by_name'     => ['required', 'string', 'max:255'],
            'presided_by_role'     => ['required', 'string', 'max:255'],
            'result'               => ['required', 'string', 'max:2000'],
            'is_agreement_reached' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'complaint_id'         => 'plainte',
            'session_date'         => 'date de la session',
            'presided_by_name'     => 'nom du président de séance',
            'presided_by_role'     => 'fonction du président de séance',
            'result'               => 'compte-rendu / résultat',
            'is_agreement_reached' => 'accord trouvé',
        ];
    }
}
