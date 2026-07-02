<?php

namespace App\Http\Controllers;

use App\Models\FrequentRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrequentRecipientController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recipients = FrequentRecipient::getForUser($user);

        return response()->json($recipients->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'lastname' => $r->lastname,
            'document' => $r->document,
            'phone' => $r->phone,
            'alt_phone' => $r->alt_phone,
            'address' => $r->address,
            'neighborhood' => $r->neighborhood,
            'locality' => $r->locality,
            'city' => $r->city,
            'notes' => $r->notes,
            'use_count' => $r->use_count,
        ]));
    }

    public function search(Request $request)
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

        return response()->json($recipients->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'lastname' => $r->lastname,
            'document' => $r->document,
            'phone' => $r->phone,
            'alt_phone' => $r->alt_phone,
            'address' => $r->address,
            'neighborhood' => $r->neighborhood,
            'locality' => $r->locality,
            'city' => $r->city,
            'notes' => $r->notes,
            'use_count' => $r->use_count,
        ]));
    }

    public function store(Request $request)
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

    public function destroy(FrequentRecipient $frequentRecipient)
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
}