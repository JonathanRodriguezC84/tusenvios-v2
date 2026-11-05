<?php

use App\Models\MerchandiseLoan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $loans = MerchandiseLoan::where('loan_number', 'PREST-0001')->with('items')->get();
        foreach ($loans as $loan) {
            $newTotal = 0;
            foreach ($loan->items as $item) {
                $item->update(['unit_price' => 15000]);
                $newTotal += $item->quantity_lent * 15000;
            }
            $loan->update(['total_value' => $newTotal]);
        }
    }

    public function down(): void
    {
        //
    }
};
