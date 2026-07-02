<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->string('start_mode')->default('paid')->after('status');
            $table->unsignedSmallInteger('trial_guide_limit')->default(0)->after('start_mode');
            $table->unsignedSmallInteger('trial_guide_used')->default(0)->after('trial_guide_limit');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['start_mode', 'trial_guide_limit', 'trial_guide_used']);
        });
    }
};