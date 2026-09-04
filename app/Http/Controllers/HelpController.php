<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $user = auth()->user();
        $tenant = $user->tenant ?: $user->affiliatedCompany?->tenant;

        $rawPhone = config('services.whatsapp.support_phone', '')
            ?: ($tenant?->brand_whatsapp ?? '');

        $whatsapp = $this->normalizePhone($rawPhone);
        $waText = rawurlencode("Hola! Necesito ayuda con mi cuenta de Tus Envios. Mi tienda es: ".($tenant?->name ?? $user->name));

        return view('help.index', compact('whatsapp', 'waText', 'tenant'));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (strlen($digits) === 10) {
            $digits = '57'.$digits;
        }
        return $digits;
    }
}
