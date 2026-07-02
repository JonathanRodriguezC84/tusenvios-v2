<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('brand_color', 20)->default('#0047D9')->after('logo_path');
            $table->string('brand_whatsapp')->nullable()->after('brand_color');
            $table->string('brand_instagram')->nullable()->after('brand_whatsapp');
            $table->string('brand_website')->nullable()->after('brand_instagram');
            $table->string('brand_message')->nullable()->after('brand_website');
        });

        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('status');
            $table->string('brand_color', 20)->default('#0047D9')->after('logo_path');
            $table->string('brand_whatsapp')->nullable()->after('brand_color');
            $table->string('brand_instagram')->nullable()->after('brand_whatsapp');
            $table->string('brand_website')->nullable()->after('brand_instagram');
            $table->string('brand_message')->nullable()->after('brand_website');
        });
    }

    public function down(): void
    {
        Schema::table('affiliated_companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'brand_color',
                'brand_whatsapp',
                'brand_instagram',
                'brand_website',
                'brand_message',
            ]);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'brand_color',
                'brand_whatsapp',
                'brand_instagram',
                'brand_website',
                'brand_message',
            ]);
        });
    }
};
