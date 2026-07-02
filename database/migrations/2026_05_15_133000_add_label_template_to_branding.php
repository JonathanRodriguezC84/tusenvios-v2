<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('label_template')->default('classic')->after('brand_message');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->string('label_template')->default('classic')->after('brand_message');
        });
    }

    public function down(): void
    {
        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->dropColumn('label_template');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('label_template');
        });
    }
};
