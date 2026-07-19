<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Audit;

class ShipmentPrintController extends Controller
{
    public function print(Request $request, Shipment $shipment): \Illuminate\View\View
    {
        $this->authorize('print', $shipment);

        $shipment->load(['affiliatedCompany', 'tenant', 'deliveryZone']);

        if ($shipment->status === 'created') {
            $shipment->update(['status' => 'printed']);

            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => Auth::id(),
                'status' => 'printed',
                'location' => 'Sistema',
                'notes' => 'Guia impresa.',
            ]);

            Audit::log('shipment.printed', $shipment, "Guia {$shipment->guide_number} impresa.");
        }

        $printFormats = $this->printFormats();
        $defaultFormat = $shipment->affiliatedCompany?->default_print_format
            ?? $shipment->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($request->query('format'), $printFormats)
            ? $request->query('format')
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        return view('shipments.print', compact('shipment', 'printFormats', 'selectedPrintFormat', 'printFormat'));
    }

    public function printPdf(Request $request, Shipment $shipment): \Illuminate\Http\Response
    {
        $this->authorize('print', $shipment);
        $shipment->load(['affiliatedCompany', 'tenant', 'deliveryZone']);

        $printFormats = $this->printFormats();
        $defaultFormat = $shipment->affiliatedCompany?->default_print_format
            ?? $shipment->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($request->query('format'), $printFormats)
            ? $request->query('format')
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shipments.print-pdf', compact('shipment', 'printFormat', 'selectedPrintFormat'));

        if (in_array($selectedPrintFormat, ['letter', 'a4'], true)) {
            $pdf->setPaper($selectedPrintFormat === 'a4' ? 'a4' : 'letter');
        }

        return $pdf->download('guia-'.$shipment->guide_number.'.pdf');
    }

    public function bulkPrint(Request $request): \Illuminate\View\View
    {
        $validated = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'format' => ['nullable', 'string'],
        ]);

        $shipments = Shipment::query()
            ->with(['affiliatedCompany', 'tenant', 'deliveryZone'])
            ->visibleTo(Auth::user())
            ->whereIn('id', $validated['shipment_ids'])
            ->get();

        foreach ($shipments as $shipment) {
            if ($shipment->status === 'created') {
                $shipment->update(['status' => 'printed']);

                ShipmentEvent::query()->create([
                    'shipment_id' => $shipment->id,
                    'user_id' => Auth::id(),
                    'status' => 'printed',
                    'location' => 'Sistema',
                    'notes' => 'Guia impresa en lote.',
                ]);

                Audit::log('shipment.printed', $shipment, "Guia {$shipment->guide_number} impresa en lote.");
            }
        }

        $printFormats = $this->printFormats();
        $defaultFormat = Auth::user()->affiliatedCompany?->default_print_format
            ?? Auth::user()->tenant?->default_print_format
            ?? '100x150';
        if (!array_key_exists($defaultFormat, $printFormats)) {
            $defaultFormat = '100x150';
        }
        $selectedPrintFormat = array_key_exists($validated['format'] ?? null, $printFormats)
            ? $validated['format']
            : $defaultFormat;
        $printFormat = $printFormats[$selectedPrintFormat];

        return view('shipments.bulk-print', compact('shipments', 'printFormats', 'selectedPrintFormat', 'printFormat'));
    }

    private function printFormats(): array
    {
        return [
            '100x150' => [
                'label' => 'Etiqueta 100 x 150 mm',
                'short_label' => '100 x 150',
                'page' => '100mm 150mm',
                'width' => '100mm',
                'height' => '150mm',
                'scale' => '1',
                'padding' => '0',
                'help' => 'Impresora termica estandar.',
                'multi' => false,
            ],
            '100x100' => [
                'label' => 'Etiqueta 100 x 100 mm',
                'short_label' => '100 x 100',
                'page' => '100mm 100mm',
                'width' => '100mm',
                'height' => '100mm',
                'scale' => '.66',
                'padding' => '0',
                'help' => 'Etiqueta cuadrada.',
                'multi' => false,
            ],
            '80x50' => [
                'label' => 'Etiqueta 50 x 80 mm (vertical)',
                'short_label' => '50 x 80',
                'page' => '50mm 80mm',
                'width' => '50mm',
                'height' => '80mm',
                'scale' => '.53',
                'padding' => '0',
                'help' => 'Etiqueta termica pequena, vertical.',
            ],
            'half-letter' => [
                'label' => 'Media carta',
                'short_label' => 'Media carta',
                'page' => '5.5in 8.5in',
                'width' => '5.5in',
                'height' => '8.5in',
                'scale' => '1.2',
                'padding' => '10mm',
                'help' => 'Impresora normal, media hoja.',
                'multi' => false,
            ],
            'letter' => [
                'label' => 'Carta (1 por hoja)',
                'short_label' => 'Carta',
                'page' => 'letter',
                'width' => '8.5in',
                'height' => '11in',
                'scale' => '1.35',
                'padding' => '10mm',
                'help' => 'Impresora normal de oficina, 1 guia por hoja.',
                'multi' => false,
            ],
        ];
    }
}
