<?php

declare(strict_types=1);

namespace App\Http\Requests\Complaint;

use App\Domains\Complaint\Enums\ComplaintChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complaints.create');
    }

    public function rules(): array
    {
        return [
            'complaint_type_id'   => ['required', 'integer', 'exists:complaint_types,id'],
            'site_id'             => ['nullable', 'integer', 'exists:sites,id'],
            'is_anonymous'        => ['boolean'],
            'complainant_name'    => ['required_unless:is_anonymous,1', 'nullable', 'string', 'max:200'],
            'complainant_age'     => ['nullable', 'integer', 'min:0', 'max:120'],
            'complainant_gender'  => ['nullable', 'in:M,F'],
            'complainant_locality'=> ['nullable', 'string', 'max:200'],
            'complainant_phone'   => ['nullable', 'string', 'max:20'],
            'subject'             => ['required', 'string', 'max:300'],
            'description'         => ['required', 'string', 'min:20'],
            'incident_date'       => ['required', 'date', 'before_or_equal:today'],
            'accused_party'       => ['nullable', 'string', 'max:300'],
            'channel'             => ['required', new Enum(ComplaintChannel::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'complaint_type_id'  => 'type de plainte',
            'site_id'            => 'site concerné',
            'complainant_name'   => 'nom du plaignant',
            'subject'            => 'objet de la plainte',
            'description'        => 'description',
            'incident_date'      => 'date des faits',
            'channel'            => 'canal de réception',
        ];
    }
}
