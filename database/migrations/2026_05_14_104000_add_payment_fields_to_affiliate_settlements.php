<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_settlements', function (Blueprint $table) {
            $table->foreignId('paid_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('closed_at');
            $table->string('payment_reference')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_settlements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn(['paid_at', 'payment_reference']);
        });
    }
};
