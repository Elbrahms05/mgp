<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles')->orderBy('name');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($w) => $w
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('email', 'ilike', "%{$q}%")
            );
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('pages.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('pages.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'password'  => Hash::make($data['password']),
            'locale'    => $data['locale'] ?? 'fr',
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->assignRole($data['role']);

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Utilisateur \"{$user->name}\" créé avec succès.");
    }

    public function show(User $user): View
    {
        $user->load('roles');

        $activities = \Spatie\Activitylog\Models\Activity::causedBy($user)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('pages.users.show', compact('user', 'activities'));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('pages.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'locale'    => $data['locale'] ?? $user->locale,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.show', $user)
            ->with('success', "Utilisateur \"{$user->name}\" mis à jour.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', "Utilisateur \"{$name}\" supprimé.");
    }

    public function roles(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('pages.users.roles', compact('roles'));
    }
}
