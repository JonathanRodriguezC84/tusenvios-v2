<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('guide_prefix', 3)->nullable()->after('subdomain');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->string('guide_prefix', 3)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('guide_prefix');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->dropColumn('guide_prefix');
        });
    }
};
