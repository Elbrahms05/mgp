<?php

declare(strict_types=1);

namespace App\Http\Requests\Vbg;

use App\Domains\Vbg\Enums\ReferralServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vbg.receive');
    }

    public function rules(): array
    {
        return [
            'service_type'     => ['required', new Enum(ReferralServiceType::class)],
            'provider_name'    => ['required', 'string', 'max:200'],
            'provider_contact' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'service_type'  => 'type de service',
            'provider_name' => 'prestataire',
        ];
    }
}
