<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q    = trim($request->get('q', ''));
        $user = $request->user();

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Clientes — disponible para todos los roles que tienen clients.view
        if ($user->can('clients.view')) {
            Client::query()
                ->where(fn ($q2) => $q2
                    ->where('business_name', 'ilike', "%{$q}%")
                    ->orWhere('trade_name', 'ilike', "%{$q}%")
                    ->orWhere('document_number', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                )
                ->where('is_active', true)
                ->orderBy('business_name')
                ->limit(6)
                ->get(['id', 'business_name', 'document_number', 'type', 'city'])
                ->each(fn ($c) => $results->push([
                    'type'       => 'client',
                    'group'      => 'Clientes',
                    'id'         => $c->id,
                    'label'      => $c->business_name,
                    'sublabel'   => $c->document_number . ($c->city ? ' · ' . $c->city : ''),
                    'badge'      => $c->type === 'juridica' ? 'Jurídica' : 'Natural',
                    'badgeClass' => $c->type === 'juridica' ? 'bg-light-primary text-primary' : 'bg-light-secondary text-secondary',
                    'url'        => '/clients/' . $c->id,
                    'icon'       => $c->type === 'juridica' ? 'ti ti-building' : 'ti ti-user',
                ]));
        }

        // Usuarios — solo para admin (users.manage)
        if ($user->can('users.manage')) {
            User::query()
                ->where(fn ($q2) => $q2
                    ->where('name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                )
                ->orderBy('name')
                ->limit(4)
                ->get(['id', 'name', 'email', 'is_active'])
                ->each(fn ($u) => $results->push([
                    'type'       => 'user',
                    'group'      => 'Usuarios del sistema',
                    'id'         => $u->id,
                    'label'      => $u->name,
                    'sublabel'   => $u->email,
                    'badge'      => $u->is_active ? 'Activo' : 'Inactivo',
                    'badgeClass' => $u->is_active ? 'bg-light-success text-success' : 'bg-light-danger text-danger',
                    'url'        => '/users',
                    'icon'       => 'ti ti-user-circle',
                ]));
        }

        // Log de actividades — solo para admin (activity-logs.view)
        if ($user->can('activity-logs.view')) {
            ActivityLog::query()
                ->where(fn ($q2) => $q2
                    ->where('description', 'ilike', "%{$q}%")
                    ->orWhere('subject_label', 'ilike', "%{$q}%")
                )
                ->orderByDesc('created_at')
                ->limit(4)
                ->get(['id', 'action', 'module', 'description', 'subject_label'])
                ->each(fn ($l) => $results->push([
                    'type'       => 'log',
                    'group'      => 'Log de actividades',
                    'id'         => $l->id,
                    'label'      => $l->subject_label ?? $l->module,
                    'sublabel'   => $l->description,
                    'badge'      => $l->action,
                    'badgeClass' => match ($l->action) {
                        'created'  => 'bg-light-success text-success',
                        'updated'  => 'bg-light-info text-info',
                        'deleted'  => 'bg-light-danger text-danger',
                        default    => 'bg-light-secondary text-secondary',
                    },
                    'url'        => '/activity-logs',
                    'icon'       => 'ti ti-tool',
                ]));
        }

        return response()->json(['results' => $results->values()]);
    }
}
