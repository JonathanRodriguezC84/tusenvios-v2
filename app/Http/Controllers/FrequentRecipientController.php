<?php

namespace App\Http\Controllers;

use App\Models\FrequentRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrequentRecipientController extends Controller
{
    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $query = $this->recipientQuery($user, $filters);

        if ($request->expectsJson()) {
            $recipients = (clone $query)
                ->orderByDesc('use_count')
                ->orderByDesc('updated_at')
                ->take(50)
                ->get();

            return response()->json($recipients->map(fn ($r) => $this->recipientPayload($r)));
        }

        $summaryBase = $this->recipientQuery($user, $filters);
        $summary = [
            'total' => (clone $summaryBase)->count(),
            'repeat' => (clone $summaryBase)->where('use_count', '>', 1)->count(),
            'uses' => (clone $summaryBase)->sum('use_count'),
            'topCity' => (clone $summaryBase)
                ->whereNotNull('city')
                ->selectRaw('city, COUNT(*) as total')
                ->groupBy('city')
                ->orderByDesc('total')
                ->value('city'),
        ];

        $recipients = $query
            ->orderByDesc('use_count')
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $cityToDepartment = \App\Models\City::query()
            ->join('departments', 'departments.id', '=', 'cities.department_id')
            ->select('cities.name as city', 'departments.name as dep')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->city => $row->dep]);

        return view('recipients.index', compact('recipients', 'filters', 'summary', 'cityToDepartment'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $query = $this->recipientQuery($user, $filters)
            ->orderByDesc('use_count')
            ->orderByDesc('updated_at');

        $cityToDepartment = \App\Models\City::query()
            ->join('departments', 'departments.id', '=', 'cities.department_id')
            ->select('cities.name as city', 'departments.name as dep')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->city => $row->dep]);

        $fileName = 'clientes-frecuentes-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $cityToDepartment) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nombre',
                'Apellido',
                'Documento',
                'Telefono',
                'Telefono alterno',
                'Direccion',
                'Barrio',
                'Localidad',
                'Ciudad',
                'Departamento',
                'Usos',
                'Notas',
                'Ultima actualizacion',
            ]);

            $query->chunk(200, function ($recipients) use ($handle, $cityToDepartment) {
                foreach ($recipients as $recipient) {
                    $cityName = $recipient->city ?: $recipient->locality;

                    fputcsv($handle, [
                        $recipient->name,
                        $recipient->lastname,
                        $recipient->document,
                        $recipient->phone,
                        $recipient->alt_phone,
                        $recipient->address,
                        $recipient->neighborhood,
                        $recipient->locality,
                        $recipient->city,
                        $cityToDepartment[$cityName] ?? '',
                        $recipient->use_count,
                        $recipient->notes,
                        $recipient->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $q = $request->get('q', '');

        $recipients = FrequentRecipient::query()
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id);
                if ($user->affiliated_company_id) {
                    $query->orWhere('affiliated_company_id', $user->affiliated_company_id);
                }
            })
            ->when($q, fn ($query) => $query->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('document', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
            ))
            ->orderByDesc('use_count')
            ->orderByDesc('updated_at')
            ->take(20)
            ->get();

        return response()->json($recipients->map(fn ($r) => $this->recipientPayload($r)));
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'alt_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:100',
            'locality' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['affiliated_company_id'] = $user->affiliated_company_id;
        $validated['use_count'] = 1;

        $recipient = FrequentRecipient::create($validated);

        return response()->json($recipient, 201);
    }

    public function destroy(FrequentRecipient $frequentRecipient): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        abort_unless(
            $frequentRecipient->tenant_id === $user->tenant_id
            || ($user->affiliated_company_id && $frequentRecipient->affiliated_company_id === $user->affiliated_company_id),
            403
        );

        $frequentRecipient->delete();
        return response()->json(['ok' => true]);
    }

    private function recipientQuery($user, array $filters = [])
    {
        return FrequentRecipient::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id);
                if ($user->affiliated_company_id) {
                    $query->orWhere('affiliated_company_id', $user->affiliated_company_id);
                }
            }))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('document', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
            ));
    }

    private function recipientPayload(FrequentRecipient $recipient): array
    {
        [$name, $lastname] = $recipient->normalizedNameParts();

        return [
            'id' => $recipient->id,
            'name' => $name,
            'lastname' => $lastname,
            'document' => $recipient->document,
            'phone' => $recipient->phone,
            'alt_phone' => $recipient->alt_phone,
            'address' => $recipient->address,
            'neighborhood' => $recipient->neighborhood,
            'locality' => $recipient->locality,
            'city' => $recipient->city,
            'notes' => $recipient->notes,
            'use_count' => $recipient->use_count,
        ];
    }
}
