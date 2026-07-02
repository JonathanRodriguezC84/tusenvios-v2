<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $logs = $this->filteredLogs($filters)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->whereHas('user', fn ($q) => $q->where('tenant_id', Auth::user()->tenant_id)))
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->when(! Auth::user()->isSuperAdmin(), fn ($q) => $q->where('tenant_id', Auth::user()->tenant_id))
            ->whereIn('id', AuditLog::query()->select('user_id')->whereNotNull('user_id'))
            ->orderBy('name')
            ->get();

        return view('audit-logs.index', compact('logs', 'filters', 'actions', 'users'));
    }

    public function export(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isTenantAdmin(), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:80'],
            'user_id' => ['nullable', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $fileName = 'auditoria-tus-envios-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Fecha', 'Usuario', 'Accion', 'Descripcion', 'Detalles']);

            $this->filteredLogs($filters)
                ->latest()
                ->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->user?->name ?? 'Sistema',
                            $log->action,
                            $log->description,
                            $log->properties ? json_encode($log->properties) : '',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredLogs(array $filters)
    {
        return AuditLog::query()
            ->with('user')
            ->when(! Auth::user()->isSuperAdmin(), fn ($query) => $query->whereHas('user', fn ($q) => $q->where('tenant_id', Auth::user()->tenant_id)))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('description', 'like', "%{$search}%"))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to));
    }
}