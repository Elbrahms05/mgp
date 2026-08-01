<?php

declare(strict_types=1);

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('workers.manage');
    }

    public function rules(): array
    {
        return [
            'worker_type'   => ['required', 'string', 'in:direct,contractor'],
            'company_id'    => ['nullable', 'exists:companies,id'],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'worker_name'   => ['required', 'string', 'max:255'],
            'worker_phone'  => ['nullable', 'string', 'max:20'],
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'min:10'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function attributes(): array
    {
        return [
            'worker_type'   => 'type de travailleur',
            'employer_name' => 'employeur / entreprise',
            'worker_name'   => 'nom du travailleur',
            'worker_phone'  => 'téléphone',
            'subject'       => 'objet de la plainte',
            'description'   => 'description',
            'incident_date' => 'date des faits',
        ];
    }
}
