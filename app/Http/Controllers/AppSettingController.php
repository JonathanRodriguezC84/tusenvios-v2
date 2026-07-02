<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppSettingController extends Controller
{
    public function edit()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $settings = AppSetting::current();

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'tracking_url' => ['required', 'string', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'print_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = AppSetting::current();
        $settings->update($validated);

        Audit::log('settings.updated', $settings, 'Configuracion general actualizada.');

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Configuracion actualizada correctamente.');
    }
}
