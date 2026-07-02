<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('affiliated_company_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->string('role')->default('superadmin')->after('email');
            $table->string('status')->default('active')->after('role');

            $table->index(['tenant_id', 'role']);
            $table->index(['affiliated_company_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('affiliated_company_id');
            $table->dropColumn(['role', 'status']);
        });
    }
};
