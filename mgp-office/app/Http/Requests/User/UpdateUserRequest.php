<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
            'role'      => ['required', 'string', 'exists:roles,name'],
            'locale'    => ['nullable', 'string', 'in:fr,ha,zr,tq,kr,ff'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => 'nom complet',
            'email'    => 'adresse e-mail',
            'phone'    => 'téléphone',
            'password' => 'mot de passe',
            'role'     => 'rôle',
            'locale'   => 'langue',
        ];
    }
}
