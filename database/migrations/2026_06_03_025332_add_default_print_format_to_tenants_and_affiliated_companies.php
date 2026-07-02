<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('default_print_format')->default('100x150')->after('label_template');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->string('default_print_format')->default('100x150')->after('label_template');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('default_print_format');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->dropColumn('default_print_format');
        });
    }
};