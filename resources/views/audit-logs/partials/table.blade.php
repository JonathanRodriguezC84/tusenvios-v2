<section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 p-4 lg:p-5">
        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Actividad administrativa</p>
        <h3 class="text-lg font-semibold text-gray-950">Registro de auditoria</h3>
    </div>

    <form method="GET" class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex flex-wrap gap-3 items-end">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar accion..." class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700 w-40">
        <select name="action" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <option value="">Todas las acciones</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <select name="user_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
            <option value="">Todos los usuarios</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
        <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
        <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Filtrar</button>
        <a href="{{ Auth::user()->isSuperAdmin() ? route('audit-logs.index') : route('audit-logs.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar</a>
    </form>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-full text-sm">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Accion</th>
                    <th>Descripcion</th>
                    <th width="60">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-semibold text-gray-950">{{ $log->user?->name ?? 'Sistema' }}</td>
                        <td>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $log->action }}</span>
                        </td>
                        <td class="text-xs text-gray-600">{{ $log->description }}</td>
                        <td>
                            @if ($log->properties)
                                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="rounded border border-gray-300 bg-white px-2 py-0.5 text-3xs font-semibold text-gray-500 hover:bg-gray-50">Ver</button>
                                <pre class="hidden mt-1 text-3xs text-gray-500 max-w-xs overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-gray-500 py-8">No hay registros de auditoria.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-200 px-5 py-4">{{ $logs->links() }}</div>
</section>
