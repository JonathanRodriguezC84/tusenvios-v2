<?php

namespace App\Console\Commands;

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class VerifyDashboardData extends Command
{
    protected $signature = 'dashboard:verify {--user=2 : ID del usuario para simular}';
    protected $description = 'Verifica las metricas y tarjetas del dashboard para el usuario principal';

    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $user = User::find($userId);
        if (! $user) {
            $this->error('Usuario 2 no encontrado');
            return 1;
        }

        Auth::login($user);

        $controller = new DashboardController();
        $response = $controller->__invoke();
        $data = $response->getData();

        $this->info("=== VERIFICACIÓN DASHBOARD (Usuario: {$user->name}, Role: {$user->role}, Tenant: {$user->tenant_id}) ===");
        $this->line("Rango: " . ($data['dateRange']['label'] ?? ''));

        $this->newLine();
        $this->info("--- RESUMEN DE OPERACIÓN ---");
        $this->line("Guías creadas (hoy/periodo): " . ($data['metrics']['shipments_today'] ?? 0));
        $this->line("Guías periodo anterior: " . ($data['metrics']['shipments_yesterday'] ?? 0));
        $this->line("Delta guías: " . ($data['metrics']['delta'] ?? 0));
        $this->line("Costo de productos: $" . number_format($data['productFinancials']['cost'] ?? 0, 0, ',', '.'));
        $this->line("Ingreso por ventas: $" . number_format($data['productFinancials']['sales'] ?? 0, 0, ',', '.'));
        $this->line("Utilidad: $" . number_format($data['productFinancials']['profit'] ?? 0, 0, ',', '.'));
        $this->line("Margen: " . ($data['productFinancials']['margin'] ?? 0) . "%");
        $this->line("Unidades vendidas: " . ($data['productFinancials']['units'] ?? 0));

        $this->newLine();
        $this->info("--- MICRO-GRÁFICOS FINANCIEROS (SPARKLINES) ---");
        $this->line("Sales SVG Path: " . ($data['chartFinancialsByDay']['sales_line'] ?? 'N/A'));
        $this->line("Cost SVG Path:  " . ($data['chartFinancialsByDay']['cost_line'] ?? 'N/A'));
        $this->line("Profit SVG Path:" . ($data['chartFinancialsByDay']['profit_line'] ?? 'N/A'));
        foreach ($data['chartFinancialsByDay']['days'] as $d) {
            $this->line("  {$d['label']} ({$d['full']}): Ventas $" . number_format($d['sales'], 0, ',', '.') . " | Costo $" . number_format($d['cost'], 0, ',', '.') . " | Utilidad $" . number_format($d['profit'], 0, ',', '.'));
        }

        $this->newLine();
        $this->info("--- RESUMEN DE LOGÍSTICA ---");
        $this->line("En camino (total operación): " . ($data['metrics']['in_transit'] ?? 0));
        $this->line("  -> En bodega: " . ($data['metrics']['warehouse'] ?? 0));
        $this->line("  -> En ruta: " . ($data['metrics']['on_route_only'] ?? 0));
        $this->line("Entregadas: " . ($data['deliveryRate']['delivered'] ?? 0) . " de " . ($data['deliveryRate']['total'] ?? 0) . " (" . ($data['deliveryRate']['rate'] ?? 0) . "%)");
        $this->line("Devoluciones pendientes: " . ($data['metrics']['return_pending'] ?? 0));
        $this->line("Devoluciones totales: " . ($data['metrics']['returned_total'] ?? 0));
        $this->line("Canceladas: " . ($data['metrics']['cancelled'] ?? 0));

        $this->newLine();
        $this->info("--- ALERTAS (REQUIEREN TU ATENCIÓN) ---");
        $this->line("Total alertas activas: " . count($data['alerts']));
        foreach ($data['alerts'] as $alert) {
            $this->line(" - [{$alert['label']}]: {$alert['count']}");
        }

        $this->newLine();
        $this->info("--- RESUMEN OPERACIÓN MENSUAL ---");
        $this->line("Mes: " . ($data['chartMonthToDate']['month'] ?? ''));
        $this->line("Guías en el mes: " . ($data['chartMonthToDate']['created'] ?? 0));
        $this->line("Ingresos del mes: $" . number_format($data['chartMonthToDate']['revenue'] ?? 0, 0, ',', '.'));
        $this->line("Proyección fin de mes: $" . number_format($data['chartMonthToDate']['expected_revenue'] ?? 0, 0, ',', '.'));

        $this->newLine();
        $this->info("--- TOP PRODUCTOS MÁS ENVIADOS ---");
        foreach ($data['chartTopProducts'] as $prod) {
            $this->line(" - {$prod['name']}: {$prod['count']} unidades ({$prod['pct']}%)");
        }

        $this->newLine();
        $this->info("--- GRÁFICA SEMANAL (SPARKLINE) ---");
        foreach ($data['chartShipmentsByDay']['days'] as $day) {
            $this->line(" {$day['label']} ({$day['full']}): {$day['count']} guías");
        }

        return 0;
    }
}
