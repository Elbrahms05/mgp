<?php

declare(strict_types=1);

namespace App\Http\Requests\Committee;

use App\Domains\Committee\Enums\CommitteeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCommitteeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('committees.manage');
    }

    public function rules(): array
    {
        $committeeId = $this->route('committee')?->id;

        return [
            'name'              => ['required', 'string', 'max:200'],
            'level'             => ['required', new Enum(CommitteeLevel::class)],
            'locality_id'       => [
                Rule::requiredIf(fn () => in_array($this->level, [CommitteeLevel::CBGP->value, CommitteeLevel::CCGP->value], true)),
                'nullable',
                'integer',
                'exists:localities,id',
            ],
            'installation_date' => ['required', 'date'],
            'is_active'         => ['boolean'],
            'notes'             => ['nullable', 'string', 'max:2000'],

            // Membres
            'members'               => ['nullable', 'array'],
            'members.*.full_name'   => ['required_with:members', 'string', 'max:200'],
            'members.*.role'        => ['required_with:members', 'string', 'max:100'],
            'members.*.gender'      => ['required_with:members', 'in:M,F'],
            'members.*.phone'       => ['nullable', 'string', 'max:20'],
            'members.*.election_mode'       => ['required_with:members', 'string', 'max:100'],
            'members.*.is_vbg_focal_point'  => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'              => 'nom du comité',
            'level'             => 'niveau',
            'locality_id'       => 'localité',
            'installation_date' => 'date d\'installation',
            'members.*.full_name'  => 'nom du membre',
            'members.*.role'       => 'rôle du membre',
            'members.*.gender'     => 'genre du membre',
            'members.*.election_mode' => 'mode d\'élection',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $members = $this->input('members', []);
            if (empty($members)) {
                return;
            }

            $focalPoints = array_filter($members, fn ($m) => ($m['is_vbg_focal_point'] ?? false));
            $genders = array_column(array_values($focalPoints), 'gender');

            $hasM = in_array('M', $genders, true);
            $hasF = in_array('F', $genders, true);

            if (!$hasM || !$hasF) {
                $v->errors()->add('members', 'Le comité doit avoir au moins 1 point focal VBG homme et 1 point focal VBG femme.');
            }
        });
    }
}
