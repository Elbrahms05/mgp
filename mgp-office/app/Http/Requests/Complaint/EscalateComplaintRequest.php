<?php

declare(strict_types=1);

namespace App\Http\Requests\Complaint;

use App\Domains\Committee\Enums\CommitteeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class EscalateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complaints.escalate');
    }

    public function rules(): array
    {
        return [
            'comment'   => ['required', 'string', 'min:10'],
            'new_level' => ['required', new Enum(CommitteeLevel::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $complaint = $this->route('complaint');
            if (! $complaint) {
                return;
            }

            $currentLevel = $complaint->current_level;
            $newLevel     = $this->new_level;

            // Hiérarchie : cbgp < ccgp < cngp
            $order = [
                CommitteeLevel::CBGP->value => 1,
                CommitteeLevel::CCGP->value => 2,
                CommitteeLevel::CNGP->value => 3,
            ];

            $currentOrder = $currentLevel ? ($order[$currentLevel->value] ?? 0) : 0;
            $newOrder     = $order[$newLevel] ?? 0;

            if ($newOrder <= $currentOrder) {
                $v->errors()->add('new_level', 'Le niveau d\'escalade doit être supérieur au niveau actuel.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'comment'   => 'commentaire',
            'new_level' => 'nouveau niveau',
        ];
    }
}
