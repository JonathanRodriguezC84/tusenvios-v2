<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $emprendeId = DB::table('subscription_plans')->where('code', 'emprende')->value('id');

        if (! $emprendeId) {
            return;
        }

        $now = now();

        DB::table('subscription_plans')
            ->where('code', 'emprende')
            ->update([
                'name' => 'Emprende',
                'monthly_price' => 19900,
                'features' => json_encode([
                    'Etiquetas ilimitadas',
                    'Guias ilimitadas',
                    'Productos frecuentes',
                    'Marca personalizada',
                    'Mensajeria propia o terceros',
                ]),
                'is_active' => true,
                'updated_at' => $now,
            ]);

        $retiredPlanIds = DB::table('subscription_plans')
            ->whereIn('code', ['control', 'business'])
            ->pluck('id');

        if ($retiredPlanIds->isNotEmpty()) {
            DB::table('tenant_subscriptions')
                ->whereIn('subscription_plan_id', $retiredPlanIds)
                ->update([
                    'subscription_plan_id' => $emprendeId,
                    'updated_at' => $now,
                ]);

            DB::table('subscription_payments')
                ->whereIn('subscription_plan_id', $retiredPlanIds)
                ->update([
                    'subscription_plan_id' => $emprendeId,
                    'updated_at' => $now,
                ]);
        }

        DB::table('subscription_plans')
            ->whereIn('code', ['control', 'business'])
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        DB::table('subscription_plans')
            ->whereIn('code', ['control', 'business'])
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};
