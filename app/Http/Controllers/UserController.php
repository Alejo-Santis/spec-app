<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use WithFlashMessage;

    public function __construct(private readonly ActivityLogService $log) {}

    public function index(): Response
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'roles'      => $u->getRoleNames(),
                'is_active'  => $u->is_active,
                'created_at' => $u->created_at?->format('d/m/Y'),
            ]);

        $roles = Role::orderBy('name')->pluck('name');

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->assignRole($data['role']);

        $this->log->log('created', 'users', "Usuario '{$user->name}' creado con rol '{$data['role']}'.", $user->id, $user->name, [
            'email' => $user->email,
            'role'  => $data['role'],
        ]);

        return back()->with(...$this->success("Usuario {$user->name} creado exitosamente."));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with(...$this->error('No puedes cambiar tu propio rol de administrador.'));
        }

        $oldRole = $user->getRoleNames()->first() ?? '—';
        $user->syncRoles([$request->role]);

        $this->log->log('updated', 'users', "Rol de '{$user->name}' cambiado de '{$oldRole}' a '{$request->role}'.", $user->id, $user->name, [
            'old_role' => $oldRole,
            'new_role' => $request->role,
        ]);

        return back()->with(...$this->success("Rol de {$user->name} actualizado a '{$request->role}'."));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with(...$this->error('No puedes desactivar tu propia cuenta.'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        $estado = $user->is_active ? 'activado' : 'desactivado';

        $this->log->log('updated', 'users', "Usuario '{$user->name}' {$estado}.", $user->id, $user->name);

        return back()->with(...$this->success("Usuario {$user->name} {$estado}."));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with(...$this->error('No puedes eliminar tu propia cuenta.'));
        }

        $name  = $user->name;
        $email = $user->email;
        $userId = $user->id;

        $user->delete();

        $this->log->log('deleted', 'users', "Usuario '{$name}' eliminado.", $userId, $name, ['email' => $email]);

        return back()->with(...$this->success("Usuario {$name} eliminado."));
    }
}
