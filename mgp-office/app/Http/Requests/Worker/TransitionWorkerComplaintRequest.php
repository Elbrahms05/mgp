<?php

declare(strict_types=1);

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;

class TransitionWorkerComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('workers.manage');
    }

    public function rules(): array
    {
        return [
            'action'  => ['required', 'string', 'in:start_review,escalate,resolve,close'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Action non reconnue.',
        ];
    }
}
