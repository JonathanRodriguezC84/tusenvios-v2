<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('RCI Envios');
            $table->string('tracking_url')->default('rci.com.co/track');
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable();
            $table->string('print_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
