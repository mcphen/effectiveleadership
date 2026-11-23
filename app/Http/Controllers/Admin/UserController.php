<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/** Comptes ayant accès à l'admin (réservé aux administrateurs). */
class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Users', [
            'users' => User::query()->orderBy('name')->get()->map(fn (User $u) => [
                ...$u->only(['id', 'name', 'email', 'role', 'is_active']),
                'created_at' => $u->created_at?->format('d/m/Y'),
            ]),
            'roles' => config('elc.roles'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(config('elc.roles')))],
            'password' => ['required', Password::defaults()],
        ]);

        User::query()->create([...$data, 'is_active' => true, 'email_verified_at' => now()]);

        return back()->with('success', 'Utilisateur créé.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(config('elc.roles')))],
            'is_active' => ['boolean'],
            'password' => ['nullable', Password::defaults()],
        ]);

        // On ne peut pas se retirer à soi-même les droits d'administration.
        if ($user->is($request->user())) {
            $data['role'] = 'admin';
            $data['is_active'] = true;
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Utilisateur modifié.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'Vous ne pouvez pas supprimer votre propre compte ici.');
        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}
