<?php

use App\Models\MerchandiseLoan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        MerchandiseLoan::where('loan_number', 'PREST-0001')
            ->with('items')
            ->get()
            ->each(function (MerchandiseLoan $loan) {
                $newDueDate = $loan->loan_date
                    ? $loan->loan_date->copy()->addDays(30)->toDateString()
                    : now()->addDays(30)->toDateString();

                $totalLoaned = (int) $loan->items->sum('quantity_loaned');
                $totalReturned = (int) $loan->items->sum('quantity_returned');
                $totalSold = (int) $loan->items->sum('quantity_sold');
                $totalProcessed = $totalReturned + $totalSold;

                $status = $loan->status;
                if ($totalLoaned > 0) {
                    if ($totalProcessed === 0) {
                        $status = 'active';
                    } elseif ($totalProcessed < $totalLoaned) {
                        $status = 'partial';
                    } elseif ($totalSold > 0 && $totalReturned === 0) {
                        $status = 'sold';
                    } elseif ($totalReturned === $totalLoaned) {
                        $status = 'returned';
                    } else {
                        $status = 'sold';
                    }
                }

                $loan->update([
                    'due_date' => $newDueDate,
                    'status' => $status,
                ]);
            });
    }

    public function down(): void
    {
        // No se revierte automáticamente el ajuste de plazo
    }
};
