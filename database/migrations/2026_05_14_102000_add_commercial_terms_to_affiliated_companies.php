<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->string('default_payment_method')->default('cash')->after('phone');
            $table->boolean('allows_cod')->default(true)->after('default_payment_method');
            $table->decimal('cod_commission_percent', 5, 2)->default(0)->after('allows_cod');
            $table->decimal('credit_limit', 12, 2)->default(0)->after('cod_commission_percent');
            $table->text('billing_notes')->nullable()->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->dropColumn([
                'default_payment_method',
                'allows_cod',
                'cod_commission_percent',
                'credit_limit',
                'billing_notes',
            ]);
        });
    }
};
