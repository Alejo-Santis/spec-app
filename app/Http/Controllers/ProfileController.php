<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use WithFlashMessage;

    public function __construct(private readonly ActivityLogService $log) {}

    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => [
                'id'    => auth()->user()->id,
                'name'  => auth()->user()->name,
                'email' => auth()->user()->email,
                'roles' => auth()->user()->getRoleNames(),
            ],
        ]);
    }

    public function updateInfo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        auth()->user()->update($data);

        $this->log->log('updated', 'profile', 'Perfil actualizado (nombre/email).', auth()->id(), auth()->user()->name);

        return back()->with(...$this->success('Perfil actualizado correctamente.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.confirmed'                => 'Las contraseñas nuevas no coinciden.',
            'password.min'                      => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        $this->log->log('updated', 'profile', 'Contraseña cambiada.', auth()->id(), auth()->user()->name);

        return back()->with(...$this->success('Contraseña actualizada correctamente.'));
    }
}
