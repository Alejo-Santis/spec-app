<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = ActivityLog::with('user:id,name,email')
            ->when($request->module, fn ($q, $m) => $q->where('module', $m))
            ->when($request->action, fn ($q, $a) => $q->where('action', $a))
            ->when($request->user_id, fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'ilike', "%{$s}%")
                ->orWhere('subject_label', 'ilike', "%{$s}%"))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        // Opciones para filtros
        $modules = ActivityLog::distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::distinct()->orderBy('action')->pluck('action');
        $users   = \App\Models\User::whereIn('id', ActivityLog::distinct()->pluck('user_id'))->get(['id', 'name']);

        return Inertia::render('ActivityLogs/Index', [
            'logs'    => $logs,
            'modules' => $modules,
            'actions' => $actions,
            'users'   => $users,
            'filters' => $request->only(['module', 'action', 'user_id', 'search', 'date_from', 'date_to']),
        ]);
    }
}
