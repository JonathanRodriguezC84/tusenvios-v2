<?php

namespace App\Http\Controllers;

use App\Models\FrequentRecipient;
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
        $quickActions = $this->quickActions($user);
        $issueAssistant = $this->issueAssistant($user);
        $dailyPlan = $this->dailyPlan($cards, $summary, $dailyMode, $startUrl, $user);
        $customerAttention = $this->customerAttention($user);
        $closingChecklist = $this->closingChecklist($cards, $summary, $customerAttention);
        $closingReportText = $this->closingReportText($closingChecklist, $summary);
        $dailyGoal = $this->dailyGoal($cards, $summary, $customerAttention, $closingChecklist, $user);
        $focusRoutine = $this->focusRoutine($dailyPlan, $dailyGoal, $closingChecklist);
        $opportunityRadar = $this->opportunityRadar($cards, $summary, $customerAttention, $quickActions, $user);
        $messageCenter = $this->messageCenter($summary, $customerAttention);

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
            'cards',
            'visibleCards',
            'summary',
            'statusLabels',
            'startUrl',
            'assistantMessage',
            'summaryText',
            'dailyMode',
            'modeContent',
            'quickActions',
            'issueAssistant',
            'dailyPlan',
            'customerAttention',
            'closingChecklist',
            'closingReportText',
            'dailyGoal',
            'focusRoutine',
            'opportunityRadar',
            'messageCenter'
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

    private function quickActions($user): array
    {
        $userIsTenant = $user->tenant_id || $user->affiliated_company_id;

        return [
            [
                'label' => 'Crear nueva guia',
                'description' => 'Registra el proximo envio cuando tengas un pedido listo.',
                'route' => route('shipments.create'),
                'show' => $user->canCreateShipments(),
            ],
            [
                'label' => 'Ver mis guias',
                'description' => 'Consulta el historial y confirma que todo siga en orden.',
                'route' => route('shipments.index'),
                'show' => true,
            ],
            [
                'label' => 'Mejorar mi marca',
                'description' => 'Revisa logo, colores y datos que ve tu cliente.',
                'route' => route('brand-settings.edit'),
                'show' => $userIsTenant,
            ],
            [
                'label' => 'Preparar productos',
                'description' => 'Deja listos productos frecuentes para crear guias mas rapido.',
                'route' => route('quick-products.index'),
                'show' => $userIsTenant && ! $user->canUseInventory(),
            ],
        ];
    }

    private function dailyPlan($cards, array $summary, string $dailyMode, string $startUrl, $user): array
    {
        $resolvedToday = $summary['printedToday'] + $summary['deliveredToday'];
        $workload = max(1, $summary['total'] + $resolvedToday);
        $progress = $summary['total'] === 0
            ? 100
            : min(95, (int) round(($resolvedToday / $workload) * 100));

        $focus = match ($dailyMode) {
            'urgent' => [
                'label' => 'Foco principal',
                'title' => 'Resolver novedades antes de crear mas guias',
                'detail' => 'Prioriza llamadas, confirmaciones y cierres para proteger la experiencia del cliente.',
            ],
            'pending' => [
                'label' => 'Foco operativo',
                'title' => 'Avanzar las guias que estan detenidas',
                'detail' => 'Trabaja por bloques: imprime, prepara, actualiza ruta y deja cada guia con siguiente paso claro.',
            ],
            default => [
                'label' => 'Foco comercial',
                'title' => 'Aprovechar el dia para vender o preparar envios',
                'detail' => 'Tu operacion esta tranquila; es buen momento para crear una guia o mejorar la marca que ve tu cliente.',
            ],
        };

        $steps = $cards
            ->filter(fn (array $card) => $card['count'] > 0)
            ->take(3)
            ->map(fn (array $card) => [
                'title' => $card['title'],
                'detail' => $card['description'],
                'count' => $card['count'],
                'action' => $card['action'],
                'route' => $card['route'],
                'tone' => $card['tone'],
            ])
            ->values();

        if ($steps->isEmpty()) {
            $fallbackTones = ['emerald', 'blue', 'amber'];
            $steps = collect($this->quickActions($user))
                ->filter(fn (array $action) => $action['show'])
                ->take(3)
                ->values()
                ->map(fn (array $action, int $index) => [
                    'title' => $action['label'],
                    'detail' => $action['description'],
                    'count' => 0,
                    'action' => $action['label'],
                    'route' => $action['route'],
                    'tone' => $fallbackTones[$index] ?? 'slate',
                ]);
        }

        return [
            'focus' => $focus,
            'progress' => $progress,
            'resolvedToday' => $resolvedToday,
            'estimatedMinutes' => $summary['total'] > 0 ? max(8, $summary['total'] * 4) : 0,
            'startUrl' => $startUrl,
            'steps' => $steps,
        ];
    }

    private function issueAssistant($user): array
    {
        $shipments = Shipment::query()
            ->visibleTo($user)
            ->whereIn('status', ['failed_delivery', 'rescheduled', 'return_pending'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        return [
            'count' => Shipment::query()
                ->visibleTo($user)
                ->whereIn('status', ['failed_delivery', 'rescheduled', 'return_pending'])
                ->count(),
            'shipments' => $shipments->map(function (Shipment $shipment) {
                $nextSteps = match ($shipment->status) {
                    'failed_delivery' => ['Llamar al cliente', 'Confirmar direccion', 'Reprogramar o devolver'],
                    'rescheduled' => ['Confirmar nueva fecha', 'Actualizar ruta', 'Avisar al cliente'],
                    'return_pending' => ['Confirmar devolucion', 'Coordinar bodega', 'Cerrar retorno'],
                    default => ['Revisar guia', 'Contactar cliente', 'Actualizar estado'],
                };

                $message = implode(' ', array_filter([
                    "Hola {$shipment->recipient_name}, te contactamos por tu envio {$shipment->guide_number}.",
                    $shipment->status === 'failed_delivery' ? 'Necesitamos confirmar tus datos para intentar la entrega nuevamente.' : null,
                    $shipment->status === 'rescheduled' ? 'Queremos confirmar la nueva fecha de entrega.' : null,
                    $shipment->status === 'return_pending' ? 'Tu pedido aparece en proceso de devolucion. Queremos ayudarte a revisar el caso.' : null,
                    'Puedes consultar el estado aqui: ' . route('tracking.show', $shipment->guide_number),
                ]));

                return [
                    'id' => $shipment->id,
                    'guide' => $shipment->guide_number,
                    'status' => $shipment->status,
                    'recipient' => trim($shipment->recipient_name . ' ' . $shipment->recipient_lastname),
                    'phone' => $shipment->recipient_phone,
                    'city' => $shipment->recipient_city ?: $shipment->recipient_locality,
                    'reason' => $shipment->issue_reason,
                    'updated' => $shipment->updated_at,
                    'route' => route('shipments.show', $shipment),
                    'tracking' => route('tracking.show', $shipment->guide_number),
                    'nextSteps' => $nextSteps,
                    'message' => $message,
                ];
            })->values(),
        ];
    }

    private function dailyGoal($cards, array $summary, array $customerAttention, array $closingChecklist, $user): array
    {
        $countFor = fn (string $key) => $cards->firstWhere('key', $key)['count'] ?? 0;
        $pendingPrint = $countFor('pending_print');
        $routePending = $countFor('route');
        $stalePending = $countFor('stale');
        $customerPending = $customerAttention['count'];

        $score = 100
            - ($summary['urgent'] * 18)
            - ($pendingPrint * 8)
            - ($routePending * 7)
            - ($stalePending * 10)
            - ($customerPending * 3);

        if ($closingChecklist['pending'] === 0) {
            $score = max(92, $score);
        }

        $score = max(0, min(100, (int) $score));

        if ($score >= 85) {
            $label = 'Operacion saludable';
            $tone = 'emerald';
            $description = 'Tu operacion esta controlada. Mantener este nivel ayuda a que el cliente vea una marca ordenada y confiable.';
        } elseif ($score >= 65) {
            $label = 'Atencion moderada';
            $tone = 'blue';
            $description = 'Hay pendientes manejables. Resolver la prioridad recomendada deja el dia mucho mas limpio.';
        } else {
            $label = 'Riesgo operativo';
            $tone = 'red';
            $description = 'Hay tareas que pueden afectar entregas o percepcion del cliente. Conviene atacarlas antes de crear mas guias.';
        }

        $priority = match (true) {
            $summary['urgent'] > 0 => [
                'title' => 'Resolver novedades primero',
                'detail' => $summary['urgent'].' guia(s) necesitan llamada, confirmacion o cierre.',
                'route' => route('shipments.index', ['status' => 'failed_delivery']),
                'action' => 'Ver novedades',
                'impact' => '+18 por guia',
            ],
            $pendingPrint > 0 => [
                'title' => 'Imprimir guias creadas',
                'detail' => $pendingPrint.' guia(s) esperan etiqueta para avanzar.',
                'route' => route('shipments.index', ['status' => 'created']),
                'action' => 'Imprimir guias',
                'impact' => '+8 por guia',
            ],
            $routePending > 0 => [
                'title' => 'Cerrar rutas abiertas',
                'detail' => $routePending.' guia(s) en camino necesitan resultado final.',
                'route' => route('shipments.index', ['status' => 'on_route']),
                'action' => 'Actualizar rutas',
                'impact' => '+7 por guia',
            ],
            $stalePending > 0 => [
                'title' => 'Revisar guias sin movimiento',
                'detail' => $stalePending.' guia(s) llevan mas de 24 horas sin actualizarse.',
                'route' => route('shipments.index'),
                'action' => 'Revisar guias',
                'impact' => '+10 por guia',
            ],
            $customerPending > 0 => [
                'title' => 'Contactar clientes pendientes',
                'detail' => $customerPending.' mensaje(s) sugeridos pueden mejorar la experiencia.',
                'route' => route('recipients.index'),
                'action' => 'Ver clientes',
                'impact' => '+3 por contacto',
            ],
            default => [
                'title' => 'Mantener la operacion limpia',
                'detail' => 'No hay bloqueos relevantes. Puedes crear una guia o preparar productos frecuentes.',
                'route' => $user->canCreateShipments() ? route('shipments.create') : route('shipments.index'),
                'action' => $user->canCreateShipments() ? 'Crear guia' : 'Ver guias',
                'impact' => 'Nivel ideal',
            ],
        };

        return [
            'score' => $score,
            'label' => $label,
            'tone' => $tone,
            'description' => $description,
            'priority' => $priority,
            'metrics' => [
                [
                    'label' => 'Cierre',
                    'value' => $closingChecklist['progress'].'%',
                    'detail' => $closingChecklist['done'].'/'.$closingChecklist['total'].' puntos',
                ],
                [
                    'label' => 'Pendientes',
                    'value' => $summary['total'],
                    'detail' => 'tareas operativas',
                ],
                [
                    'label' => 'Clientes',
                    'value' => $customerPending,
                    'detail' => 'seguimientos sugeridos',
                ],
            ],
        ];
    }

    private function focusRoutine(array $dailyPlan, array $dailyGoal, array $closingChecklist): array
    {
        $steps = collect([
            [
                'key' => 'priority',
                'label' => 'Atacar la prioridad',
                'detail' => $dailyGoal['priority']['title'],
                'minutes' => 8,
                'route' => $dailyGoal['priority']['route'],
                'action' => $dailyGoal['priority']['action'],
            ],
            [
                'key' => 'plan',
                'label' => 'Avanzar el plan del dia',
                'detail' => $dailyPlan['focus']['title'],
                'minutes' => max(6, min(18, (int) ceil($dailyPlan['estimatedMinutes'] / 2))),
                'route' => $dailyPlan['startUrl'],
                'action' => 'Continuar',
            ],
            [
                'key' => 'closing',
                'label' => 'Dejar cierre claro',
                'detail' => $closingChecklist['pending'] > 0
                    ? $closingChecklist['pending'].' punto(s) pendientes antes de cerrar.'
                    : 'Cierre operativo sin bloqueos.',
                'minutes' => 4,
                'route' => collect($closingChecklist['items'])->firstWhere('done', false)['route'] ?? $dailyPlan['startUrl'],
                'action' => $closingChecklist['pending'] > 0 ? 'Revisar cierre' : 'Ver detalle',
            ],
        ]);

        return [
            'storageKey' => 'daily-focus-'.now()->toDateString(),
            'totalMinutes' => $steps->sum('minutes'),
            'steps' => $steps->values()->all(),
        ];
    }

    private function opportunityRadar($cards, array $summary, array $customerAttention, array $quickActions, $user): array
    {
        $countFor = fn (string $key) => $cards->firstWhere('key', $key)['count'] ?? 0;
        $userIsTenant = $user->tenant_id || $user->affiliated_company_id;
        $quickProductsAction = collect($quickActions)->firstWhere('label', 'Preparar productos');

        $opportunities = collect([
            [
                'title' => 'Cuidar clientes recientes',
                'detail' => $customerAttention['count'] > 0
                    ? 'Hay mensajes listos para postventa o clientes frecuentes.'
                    : 'No hay seguimientos urgentes; manten el habito despues de cada entrega.',
                'metric' => $customerAttention['count'].' contacto(s)',
                'route' => route('recipients.index'),
                'action' => 'Ver clientes',
                'tone' => $customerAttention['count'] > 0 ? 'blue' : 'emerald',
                'show' => true,
            ],
            [
                'title' => 'Convertir entregas en confianza',
                'detail' => $summary['deliveredToday'] > 0
                    ? 'Aprovecha las entregas de hoy para pedir confirmacion o recompra.'
                    : 'Cuando tengas entregas, el sistema te ayudara a activar postventa.',
                'metric' => $summary['deliveredToday'].' entregada(s)',
                'route' => route('shipments.index', ['status' => 'delivered']),
                'action' => 'Ver entregas',
                'tone' => $summary['deliveredToday'] > 0 ? 'emerald' : 'slate',
                'show' => true,
            ],
            [
                'title' => 'Crear la siguiente venta',
                'detail' => $countFor('pending_print') === 0
                    ? 'Tu cola de impresion esta limpia; es buen momento para registrar el proximo pedido.'
                    : 'Primero imprime las guias creadas para que la operacion no se acumule.',
                'metric' => $countFor('pending_print').' por imprimir',
                'route' => $user->canCreateShipments() ? route('shipments.create') : route('shipments.index'),
                'action' => $user->canCreateShipments() ? 'Crear guia' : 'Ver guias',
                'tone' => $countFor('pending_print') === 0 ? 'blue' : 'amber',
                'show' => true,
            ],
            [
                'title' => 'Preparar productos frecuentes',
                'detail' => 'Deja productos comunes listos para crear guias mas rapido y con menos errores.',
                'metric' => 'Ahorro de tiempo',
                'route' => $quickProductsAction['route'] ?? route('shipments.create'),
                'action' => 'Preparar',
                'tone' => 'indigo',
                'show' => (bool) ($quickProductsAction['show'] ?? false),
            ],
            [
                'title' => 'Reforzar imagen profesional',
                'detail' => 'Revisa logo, colores y datos visibles para que el seguimiento se sienta mas confiable.',
                'metric' => 'Marca',
                'route' => route('brand-settings.edit'),
                'action' => 'Ver marca',
                'tone' => 'slate',
                'show' => $userIsTenant,
            ],
        ])->filter(fn (array $item) => $item['show'])->take(4)->values();

        return [
            'count' => $opportunities->count(),
            'items' => $opportunities->all(),
        ];
    }

    private function messageCenter(array $summary, array $customerAttention): array
    {
        $templates = collect([
            [
                'title' => 'Confirmar pedido',
                'label' => 'Antes de enviar',
                'detail' => 'Para confirmar datos antes de crear o despachar una guia.',
                'tone' => 'blue',
                'message' => 'Hola, gracias por tu compra. Antes de despachar tu pedido, por favor confirmame nombre completo, direccion, barrio, ciudad y telefono de contacto. Asi evitamos retrasos en la entrega.',
            ],
            [
                'title' => 'Enviar seguimiento',
                'label' => 'Guia activa',
                'detail' => 'Para compartir el link de rastreo con tono profesional.',
                'tone' => 'emerald',
                'message' => 'Hola, tu pedido ya esta en proceso. Puedes revisar el avance de tu envio con el numero de guia que te compartimos. Si tienes alguna duda, estamos atentos para ayudarte.',
            ],
            [
                'title' => 'Resolver novedad',
                'label' => $summary['urgent'] > 0 ? $summary['urgent'].' pendiente(s)' : 'Preventivo',
                'detail' => 'Para pedir confirmacion cuando una entrega necesita atencion.',
                'tone' => $summary['urgent'] > 0 ? 'red' : 'amber',
                'message' => 'Hola, te contactamos porque tu envio necesita una confirmacion para poder avanzar. Por favor revisa direccion, telefono y disponibilidad para entrega. Queremos ayudarte a completar el proceso sin inconvenientes.',
            ],
            [
                'title' => 'Postventa',
                'label' => $customerAttention['count'] > 0 ? $customerAttention['count'].' sugerido(s)' : 'Fidelizacion',
                'detail' => 'Para agradecer despues de una entrega y abrir la puerta a recompra.',
                'tone' => 'indigo',
                'message' => 'Hola, esperamos que hayas recibido muy bien tu pedido. Gracias por comprar con nosotros. Si necesitas ayuda, tienes dudas o quieres hacer un nuevo pedido, estamos atentos para atenderte.',
            ],
        ]);

        return [
            'count' => $templates->count(),
            'items' => $templates->all(),
        ];
    }

    private function customerAttention($user): array
    {
        $delivered = Shipment::query()
            ->visibleTo($user)
            ->where('status', 'delivered')
            ->where('updated_at', '>=', now()->subDays(3))
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->toBase()
            ->map(function (Shipment $shipment) {
                $recipient = trim($shipment->recipient_name . ' ' . $shipment->recipient_lastname);

                return [
                    'key' => 'delivered-'.$shipment->id,
                    'tone' => 'emerald',
                    'label' => 'Postventa',
                    'title' => $recipient ?: 'Cliente entregado',
                    'detail' => "Guia {$shipment->guide_number} entregada recientemente.",
                    'phone' => $shipment->recipient_phone,
                    'city' => $shipment->recipient_city ?: $shipment->recipient_locality,
                    'route' => route('shipments.show', $shipment),
                    'action' => 'Abrir guia',
                    'message' => implode(' ', [
                        "Hola {$shipment->recipient_name}, esperamos que hayas recibido muy bien tu pedido {$shipment->guide_number}.",
                        'Gracias por tu compra. Si necesitas algo mas, estamos atentos para ayudarte.',
                        'Seguimiento: '.route('tracking.show', $shipment->guide_number),
                    ]),
                ];
            });

        $recentShipments = Shipment::query()
            ->visibleTo($user)
            ->whereNotNull('recipient_phone')
            ->latest('created_at')
            ->take(100)
            ->get();

        $repeatCustomers = $recentShipments
            ->groupBy(fn (Shipment $shipment) => preg_replace('/\D+/', '', (string) $shipment->recipient_phone) ?: strtoupper(trim($shipment->recipient_name.' '.$shipment->recipient_lastname)))
            ->filter(fn ($shipments) => $shipments->count() > 1)
            ->sortByDesc(fn ($shipments) => $shipments->count())
            ->take(4)
            ->map(function ($shipments) {
                /** @var Shipment $shipment */
                $shipment = $shipments->first();
                $recipient = trim($shipment->recipient_name . ' ' . $shipment->recipient_lastname);

                return [
                    'key' => 'repeat-'.$shipment->id,
                    'tone' => 'blue',
                    'label' => 'Cliente frecuente',
                    'title' => $recipient ?: 'Cliente recurrente',
                    'detail' => $shipments->count().' guia(s) recientes asociadas a este cliente.',
                    'phone' => $shipment->recipient_phone,
                    'city' => $shipment->recipient_city ?: $shipment->recipient_locality,
                    'route' => route('shipments.index', ['search' => $shipment->recipient_phone]),
                    'action' => 'Ver historial',
                    'message' => implode(' ', [
                        "Hola {$shipment->recipient_name}, gracias por seguir comprando con nosotros.",
                        'Tenemos tus datos guardados para ayudarte mas rapido en tus proximos envios.',
                        'Cuando necesites un nuevo pedido, estamos atentos.',
                    ]),
                ];
            })
            ->values();

        $frequentRecipients = FrequentRecipient::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id);
                if ($user->affiliated_company_id) {
                    $query->orWhere('affiliated_company_id', $user->affiliated_company_id);
                }
            }))
            ->where('use_count', '>', 1)
            ->orderByDesc('use_count')
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->toBase()
            ->map(function (FrequentRecipient $recipient) {
                $name = trim($recipient->name . ' ' . $recipient->lastname);

                return [
                    'key' => 'frequent-'.$recipient->id,
                    'tone' => 'blue',
                    'label' => 'Cliente frecuente',
                    'title' => $name ?: 'Cliente recurrente',
                    'detail' => $recipient->use_count.' uso(s) guardados en la agenda.',
                    'phone' => $recipient->phone,
                    'city' => $recipient->city ?: $recipient->locality,
                    'route' => route('recipients.index', ['search' => $recipient->phone ?: $recipient->name]),
                    'action' => 'Ver cliente',
                    'message' => implode(' ', [
                        "Hola {$recipient->name}, gracias por seguir comprando con nosotros.",
                        'Tenemos tus datos guardados para ayudarte mas rapido en tus proximos envios.',
                        'Cuando necesites un nuevo pedido, estamos atentos.',
                    ]),
                ];
            });

        $items = $delivered
            ->merge($repeatCustomers)
            ->merge($frequentRecipients)
            ->unique(fn (array $item) => ($item['phone'] ?: $item['title']).'-'.$item['label'])
            ->take(6)
            ->values();

        return [
            'count' => $items->count(),
            'items' => $items,
        ];
    }

    private function closingChecklist($cards, array $summary, array $customerAttention): array
    {
        $countFor = fn (string $key) => $cards->firstWhere('key', $key)['count'] ?? 0;

        $items = collect([
            [
                'label' => 'Novedades resueltas',
                'detail' => $summary['urgent'] > 0
                    ? "{$summary['urgent']} pendiente(s) prioritario(s) por revisar."
                    : 'No hay novedades urgentes abiertas.',
                'done' => $summary['urgent'] === 0,
                'route' => route('shipments.index', ['status' => 'failed_delivery']),
            ],
            [
                'label' => 'Etiquetas impresas',
                'detail' => $countFor('pending_print') > 0
                    ? $countFor('pending_print').' guia(s) creadas esperan impresion.'
                    : 'No hay guias por imprimir.',
                'done' => $countFor('pending_print') === 0,
                'route' => route('shipments.index', ['status' => 'created']),
            ],
            [
                'label' => 'Rutas actualizadas',
                'detail' => $countFor('route') > 0
                    ? $countFor('route').' guia(s) en camino necesitan cierre.'
                    : 'No hay rutas abiertas sin cierre.',
                'done' => $countFor('route') === 0,
                'route' => route('shipments.index', ['status' => 'on_route']),
            ],
            [
                'label' => 'Clientes contactados',
                'detail' => $customerAttention['count'] > 0
                    ? $customerAttention['count'].' mensaje(s) sugeridos para copiar.'
                    : 'No hay seguimientos comerciales pendientes.',
                'done' => $customerAttention['count'] === 0,
                'route' => route('recipients.index'),
            ],
        ]);

        return [
            'total' => $items->count(),
            'done' => $items->where('done', true)->count(),
            'pending' => $items->where('done', false)->count(),
            'progress' => (int) round(($items->where('done', true)->count() / max(1, $items->count())) * 100),
            'items' => $items->all(),
        ];
    }

    private function closingReportText(array $closingChecklist, array $summary): string
    {
        $lines = [
            'Cierre del dia - Tus Envios',
            'Avance: '.$closingChecklist['done'].'/'.$closingChecklist['total'].' puntos completados ('.$closingChecklist['progress'].'%)',
            'Pendientes operativos: '.$summary['total'],
            'Atencion prioritaria: '.$summary['urgent'],
            'Impresas hoy: '.$summary['printedToday'],
            'Entregadas hoy: '.$summary['deliveredToday'],
            '',
            'Detalle:',
        ];

        foreach ($closingChecklist['items'] as $item) {
            $lines[] = ($item['done'] ? '[Listo] ' : '[Pendiente] ').$item['label'].' - '.$item['detail'];
        }

        return implode(PHP_EOL, $lines);
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
