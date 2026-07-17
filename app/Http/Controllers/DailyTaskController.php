<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyTaskController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $tenant = $user->tenant ?: $user->affiliatedCompany?->tenant;

        abort_unless($tenant?->daily_tasks_enabled, 404);

        $cards = collect($this->taskDefinitions())
            ->map(function (array $task) use ($user) {
                $query = $this->queryFor($user, $task);
                $shipments = (clone $query)
                    ->with(['affiliatedCompany', 'courier'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get();

                return array_merge($task, [
                    'count' => (clone $query)->count(),
                    'shipments' => $shipments,
                    'route' => $this->taskRoute($task),
                ]);
            })
            ->values();

        $summary = [
            'total' => $cards->sum('count'),
            'urgent' => $cards->whereIn('key', ['issues', 'stale'])->sum('count'),
            'printedToday' => Shipment::query()
                ->visibleTo($user)
                ->where('status', 'printed')
                ->whereDate('updated_at', today())
                ->count(),
            'deliveredToday' => Shipment::query()
                ->visibleTo($user)
                ->where('status', 'delivered')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        $startCard = $cards->first(fn (array $card) => $card['count'] > 0);
        $startShipment = $startCard ? $startCard['shipments']->first() : null;
        $fallbackUrl = $user->canCreateShipments() ? route('shipments.create') : route('shipments.index');
        $startUrl = $startShipment
            ? route('shipments.show', ['shipment' => $startShipment, 'daily' => 1])
            : ($startCard ? $startCard['route'] : $fallbackUrl);

        $assistantMessage = $this->assistantMessage($summary);
        $summaryText = $this->summaryText($cards, $summary);
        $dailyMode = $this->dailyMode($summary);
        $modeContent = $this->modeContent($dailyMode, $summary);
        $visibleCards = $dailyMode === 'all_clear'
            ? collect()
            : $cards->filter(fn (array $card) => $card['count'] > 0)->values();

        $statusLabels = [
            'created' => 'Por imprimir',
            'printed' => 'Impresa',
            'in_warehouse' => 'En bodega',
            'in_sorting' => 'En clasificacion',
            'assigned' => 'Asignada',
            'on_route' => 'En camino',
            'delivered' => 'Entregada',
            'failed_delivery' => 'Novedad',
            'rescheduled' => 'Reprogramada',
            'return_pending' => 'Por devolver',
            'returned' => 'Devuelta',
            'cancelled' => 'Cancelada',
        ];

        return view('daily-tasks.index', compact(
            'visibleCards',
            'summary',
            'statusLabels',
            'startUrl',
            'assistantMessage',
            'summaryText',
            'modeContent'
        ));
    }

    private function taskDefinitions(): array
    {
        return [
            [
                'key' => 'issues',
                'title' => 'Novedades por resolver',
                'description' => 'Guias que necesitan llamada, confirmacion o reprogramacion.',
                'statuses' => ['failed_delivery', 'rescheduled', 'return_pending'],
                'tone' => 'red',
                'priority' => 'Urgente',
                'action' => 'Resolver novedades',
            ],
            [
                'key' => 'pending_print',
                'title' => 'Guias por imprimir',
                'description' => 'Pedidos creados que todavia no tienen etiqueta impresa.',
                'statuses' => ['created'],
                'tone' => 'blue',
                'priority' => 'Hoy',
                'action' => 'Imprimir etiquetas',
            ],
            [
                'key' => 'preparation',
                'title' => 'Preparacion y bodega',
                'description' => 'Guias impresas o en preparacion que deben avanzar.',
                'statuses' => ['printed', 'in_warehouse', 'in_sorting', 'assigned'],
                'tone' => 'amber',
                'priority' => 'Operativo',
                'action' => 'Revisar preparacion',
            ],
            [
                'key' => 'route',
                'title' => 'En ruta sin cierre',
                'description' => 'Envios en camino que deben terminar en entrega o novedad.',
                'statuses' => ['on_route'],
                'tone' => 'indigo',
                'priority' => 'Seguimiento',
                'action' => 'Actualizar ruta',
            ],
            [
                'key' => 'stale',
                'title' => 'Sin movimiento reciente',
                'description' => 'Guias activas que llevan mas de 24 horas sin actualizarse.',
                'statuses' => ['created', 'printed', 'in_warehouse', 'in_sorting', 'assigned', 'on_route', 'failed_delivery', 'rescheduled', 'return_pending'],
                'tone' => 'slate',
                'priority' => 'Revisar',
                'action' => 'Ver atrasadas',
                'stale' => true,
            ],
        ];
    }

    private function queryFor($user, array $task): Builder
    {
        return Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', $task['statuses'])
            ->when($task['stale'] ?? false, fn ($query) => $query->where('updated_at', '<=', now()->subDay()));
    }

    private function taskRoute(array $task): string
    {
        if (count($task['statuses']) === 1) {
            return route('shipments.index', ['status' => $task['statuses'][0]]);
        }

        return route('shipments.index');
    }

    private function assistantMessage(array $summary): string
    {
        if ($summary['total'] === 0) {
            return 'Tu operacion esta al dia. Aprovecha para crear una guia, revisar tu marca o dejar listo tu proximo envio.';
        }

        if ($summary['urgent'] > 0) {
            return "Empieza por {$summary['urgent']} pendiente(s) prioritario(s). Resolverlos primero ayuda a evitar retrasos y devoluciones.";
        }

        return "Hoy tienes {$summary['total']} tarea(s) operativa(s). Avanza una por una para mantener tus guias actualizadas.";
    }

    private function dailyMode(array $summary): string
    {
        if ($summary['urgent'] > 0) {
            return 'urgent';
        }

        if ($summary['total'] > 0) {
            return 'pending';
        }

        return 'all_clear';
    }

    private function modeContent(string $dailyMode, array $summary): array
    {
        return match ($dailyMode) {
            'urgent' => [
                'label' => 'Atencion requerida',
                'title' => 'Hay guias que pueden afectar la entrega',
                'description' => "Resuelve primero {$summary['urgent']} pendiente(s) prioritario(s) para evitar retrasos, devoluciones o clientes sin respuesta.",
                'tone' => 'red',
            ],
            'pending' => [
                'label' => 'Trabajo pendiente',
                'title' => 'Tu operacion tiene tareas para avanzar hoy',
                'description' => "Tienes {$summary['total']} tarea(s) por atender. Empieza por la primera recomendacion y manten tus guias actualizadas.",
                'tone' => 'blue',
            ],
            default => [
                'label' => 'Todo al dia',
                'title' => 'No hay pendientes operativos',
                'description' => 'Tus guias no necesitan acciones urgentes. Puedes preparar el siguiente envio o mejorar la presentacion de tu tienda.',
                'tone' => 'emerald',
            ],
        };
    }

    private function summaryText($cards, array $summary): string
    {
        $countFor = fn (string $key) => $cards->firstWhere('key', $key)['count'] ?? 0;

        return implode(PHP_EOL, [
            'Resumen de hoy - Tus Envios',
            "Pendientes: {$summary['total']}",
            "Atencion prioritaria: {$summary['urgent']}",
            "Guias por imprimir: {$countFor('pending_print')}",
            "En preparacion: {$countFor('preparation')}",
            "En ruta sin cierre: {$countFor('route')}",
            "Sin movimiento reciente: {$countFor('stale')}",
        ]);
    }
}
