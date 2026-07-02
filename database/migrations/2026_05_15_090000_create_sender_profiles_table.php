<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliated_company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address');
            $table->string('neighborhood')->nullable();
            $table->string('locality')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_profiles');
    }
};
