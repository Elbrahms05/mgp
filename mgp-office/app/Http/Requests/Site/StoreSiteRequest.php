<?php

declare(strict_types=1);

namespace App\Http\Requests\Site;

use App\Domains\Site\Enums\SiteCategory;
use App\Domains\Site\Enums\SiteStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sites.create');
    }

    public function rules(): array
    {
        return [
            'code'                 => ['required', 'string', 'max:30', 'unique:sites,code'],
            'name'                 => ['required', 'string', 'max:200'],
            'site_type_id'         => ['required', 'integer', 'exists:site_types,id'],
            'category'             => ['required', new Enum(SiteCategory::class)],
            'locality_id'          => ['required', 'integer', 'exists:localities,id'],
            'latitude'             => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'            => ['nullable', 'numeric', 'between:-180,180'],
            'area_hectares'        => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'beneficiaries_count'  => ['nullable', 'integer', 'min:0'],
            'start_date'           => ['required', 'date'],
            'expected_end_date'    => ['required', 'date', 'after_or_equal:start_date'],
            'actual_end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'               => ['required', new Enum(SiteStatus::class)],
            'description'          => ['nullable', 'string', 'max:2000'],
            'responsible_user_id'  => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code'                => 'code site',
            'name'                => 'nom du site',
            'site_type_id'        => 'type de site',
            'category'            => 'composante PACIPA',
            'locality_id'         => 'localité',
            'start_date'          => 'date de début',
            'expected_end_date'   => 'date de fin prévue',
            'responsible_user_id' => 'responsable',
        ];
    }
}
