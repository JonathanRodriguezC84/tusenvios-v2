<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('frequent_recipients', 'lastname')) {
            Schema::table('frequent_recipients', function (Blueprint $table) {
                $table->string('lastname')->nullable()->after('name');
            });
        }

        $recipients = DB::table('frequent_recipients')
            ->where(function ($q) {
                $q->whereNull('lastname')->orWhere('lastname', '');
            })
            ->get();

        $updated = 0;

        foreach ($recipients as $recipient) {
            if (! $recipient->phone) {
                continue;
            }

            $shipment = DB::table('shipments')
                ->where('recipient_phone', $recipient->phone)
                ->whereNotNull('recipient_lastname')
                ->where('recipient_lastname', '!=', '')
                ->orderByDesc('updated_at')
                ->first(['recipient_lastname']);

            if ($shipment) {
                DB::table('frequent_recipients')
                    ->where('id', $recipient->id)
                    ->update(['lastname' => $shipment->recipient_lastname]);
                $updated++;
            }
        }
    }

    public function down(): void
    {
    }
};