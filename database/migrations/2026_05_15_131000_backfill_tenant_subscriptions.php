<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $planId = DB::table('subscription_plans')->where('code', 'emprende')->value('id');

        if (! $planId) {
            return;
        }

        $now = now();
        $tenantIds = DB::table('tenants')
            ->whereNotIn('id', DB::table('tenant_subscriptions')->select('tenant_id'))
            ->pluck('id');

        foreach ($tenantIds as $tenantId) {
            DB::table('tenant_subscriptions')->insert([
                'tenant_id' => $tenantId,
                'subscription_plan_id' => $planId,
                'status' => 'active',
                'starts_at' => $now->toDateString(),
                'ends_at' => null,
                'next_payment_at' => $now->copy()->addMonth()->toDateString(),
                'notes' => 'Suscripcion inicial creada automaticamente.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tenant_subscriptions')
            ->where('notes', 'Suscripcion inicial creada automaticamente.')
            ->delete();
    }
};
