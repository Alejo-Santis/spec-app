<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogAuthActivity
{
    public function __construct(private readonly Request $request) {}

    public function handleLogin(Login $event): void
    {
        ActivityLog::create([
            'user_id'       => $event->user->id,
            'action'        => 'login',
            'module'        => 'Auth',
            'subject_id'    => $event->user->id,
            'subject_label' => $event->user->name,
            'description'   => "Inicio de sesión: {$event->user->name} ({$event->user->email})",
            'properties'    => ['email' => $event->user->email],
            'ip_address'    => $this->request->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        ActivityLog::create([
            'user_id'       => $event->user?->id,
            'action'        => 'logout',
            'module'        => 'Auth',
            'subject_id'    => $event->user?->id,
            'subject_label' => $event->user?->name,
            'description'   => "Cierre de sesión: {$event->user?->name} ({$event->user?->email})",
            'properties'    => ['email' => $event->user?->email],
            'ip_address'    => $this->request->ip(),
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        ActivityLog::create([
            'user_id'       => null,
            'action'        => 'failed_login',
            'module'        => 'Auth',
            'subject_id'    => null,
            'subject_label' => $event->credentials['email'] ?? 'desconocido',
            'description'   => "Intento de login fallido para: " . ($event->credentials['email'] ?? 'desconocido'),
            'properties'    => ['email' => $event->credentials['email'] ?? null],
            'ip_address'    => $this->request->ip(),
        ]);
    }
}
