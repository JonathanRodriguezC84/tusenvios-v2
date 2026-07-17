<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')->update(['daily_tasks_enabled' => true]);
    }

    public function down(): void
    {
        DB::table('tenants')->update(['daily_tasks_enabled' => false]);
    }
};
