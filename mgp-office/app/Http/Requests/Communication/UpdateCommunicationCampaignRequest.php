<?php

declare(strict_types=1);

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationCampaignRequest extends FormRequest
{
    private const CAMPAIGN_TYPES = ['radio', 'display', 'training', 'sms_blast', 'event', 'other'];
    private const LANGUAGES      = ['fr', 'ha', 'zr', 'tq', 'kr', 'ff'];

    public function authorize(): bool
    {
        return $this->user()->can('communication.manage');
    }

    public function rules(): array
    {
        $types = implode(',', self::CAMPAIGN_TYPES);
        $langs = implode(',', self::LANGUAGES);

        return [
            'title'           => ['required', 'string', 'max:255'],
            'type'            => ['required', 'string', "in:{$types}"],
            'description'     => ['required', 'string', 'min:10'],
            'target_audience' => ['nullable', 'string', 'max:500'],
            'regions'         => ['nullable', 'array'],
            'regions.*'       => ['string'],
            'languages'       => ['nullable', 'array'],
            'languages.*'     => ['string', "in:{$langs}"],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'          => ['required', 'string', 'in:planned,active,completed,cancelled'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title'      => 'titre',
            'type'       => 'type de campagne',
            'start_date' => 'date de début',
            'end_date'   => 'date de fin',
            'status'     => 'statut',
        ];
    }
}
