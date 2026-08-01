<?php

declare(strict_types=1);

namespace App\Http\Requests\Vbg;

use App\Domains\Vbg\Enums\GbvimsClassification;
use App\Domains\Vbg\Enums\VbgAuthorLink;
use App\Domains\Vbg\Enums\VbgChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreVbgReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vbg.receive');
    }

    public function rules(): array
    {
        return [
            // Partie 1 - toujours requise
            'channel'           => ['required', new Enum(VbgChannel::class)],
            'is_anonymous'      => ['boolean'],
            'caller_phone'      => ['nullable', 'string', 'max:20'],
            'consent_given'     => ['boolean'],
            'survivor_age_group'=> ['required', 'in:girl_lt18,woman_gte18,boy_lt18,man_gte18'],
            'survivor_gender'   => ['required', 'in:M,F'],
            'site_id'           => ['nullable', 'integer', 'exists:sites,id'],

            // Partie 2 - conditionnelle au consentement
            'incident_date'     => ['nullable', 'date', 'before_or_equal:today'],
            'incident_zone'     => ['nullable', 'string', 'max:300'],
            'author_link'       => ['nullable', new Enum(VbgAuthorLink::class)],
            'classification'    => ['nullable', new Enum(GbvimsClassification::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'channel'            => 'canal de signalement',
            'survivor_age_group' => 'tranche d\'âge',
            'survivor_gender'    => 'genre de la survivante',
            'incident_date'      => 'date de l\'incident',
            'classification'     => 'classification GBVIMS',
        ];
    }
}
