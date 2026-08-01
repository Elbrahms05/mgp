<?php

declare(strict_types=1);

namespace App\Http\Requests\Documentation;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        $guideId = $this->route('guide')->id;

        return [
            'route_name'     => ['required', 'string', 'max:100', "unique:page_guides,route_name,{$guideId}"],
            'page_title'     => ['required', 'string', 'max:255'],
            'mgp_reference'  => ['required', 'string', 'max:255'],
            'summary'        => ['required', 'string', 'max:500'],
            'detailed_guide' => ['nullable', 'string'],
            'display_mode'   => ['required', 'string', 'in:sidebar,modal,tooltip'],
            'is_active'      => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'route_name'    => 'nom de route',
            'page_title'    => 'titre de page',
            'mgp_reference' => 'référence MGP',
            'summary'       => 'résumé',
            'display_mode'  => 'mode d\'affichage',
        ];
    }
}
