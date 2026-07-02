<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'brand_facebook')) {
                $table->string('brand_facebook')->nullable()->after('brand_instagram');
            }

            if (! Schema::hasColumn('tenants', 'brand_tiktok')) {
                $table->string('brand_tiktok')->nullable()->after('brand_facebook');
            }
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliated_companies', 'brand_facebook')) {
                $table->string('brand_facebook')->nullable()->after('brand_instagram');
            }

            if (! Schema::hasColumn('affiliated_companies', 'brand_tiktok')) {
                $table->string('brand_tiktok')->nullable()->after('brand_facebook');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliated_companies', function (Blueprint $table) {
            if (Schema::hasColumn('affiliated_companies', 'brand_tiktok')) {
                $table->dropColumn('brand_tiktok');
            }

            if (Schema::hasColumn('affiliated_companies', 'brand_facebook')) {
                $table->dropColumn('brand_facebook');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'brand_tiktok')) {
                $table->dropColumn('brand_tiktok');
            }

            if (Schema::hasColumn('tenants', 'brand_facebook')) {
                $table->dropColumn('brand_facebook');
            }
        });
    }
};