<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandSettingController extends Controller
{
    public function edit(): \Illuminate\View\View
    {
        $brandOwner = $this->brandOwner();

        abort_unless($brandOwner, 403);

        return view('brand-settings.edit', compact('brandOwner'));
    }

    public function preview(): \Illuminate\View\View
    {
        $brandOwner = $this->brandOwner();

        abort_unless($brandOwner, 403);

        return view('brand-settings.label-preview', compact('brandOwner'));
    }

    public function settings(): \Illuminate\View\View
    {
        $brandOwner = $this->brandOwner();

        abort_unless($brandOwner, 403);

        return view('brand-settings.configuration', compact('brandOwner'));
    }

    public function updateSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $brandOwner = $this->brandOwner();

        abort_unless($brandOwner, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_whatsapp' => ['nullable', 'string', 'max:80'],
            'brand_instagram' => ['nullable', 'string', 'max:80'],
            'brand_facebook' => ['nullable', 'string', 'max:80'],
            'brand_tiktok' => ['nullable', 'string', 'max:80'],
            'brand_website' => ['nullable', 'string', 'max:255'],
            'brand_message' => ['nullable', 'string', 'max:120'],
            'brand_phone' => ['nullable', 'string', 'max:80'],
            'brand_address' => ['nullable', 'string', 'max:160'],
            'brand_neighborhood' => ['nullable', 'string', 'max:120'],
            'brand_locality' => ['nullable', 'string', 'max:120'],
            'brand_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', Rule::dimensions()->maxWidth(1600)->maxHeight(1600), 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'notify_low_stock' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo') && $brandOwner->logo_path) {
            Storage::disk('public')->delete($brandOwner->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($brandOwner->logo_path) {
                Storage::disk('public')->delete($brandOwner->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('brand-logos', 'public');
        }

        unset($validated['logo'], $validated['remove_logo']);

        $brandOwner->update($validated);

        Audit::log('brand.settings.updated', $brandOwner, 'Configuracion de tienda actualizada.');

        return redirect()
            ->route('store-settings.edit')
            ->with('status', 'Configuracion actualizada correctamente.');
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $brandOwner = $this->brandOwner();

        abort_unless($brandOwner, 403);

        $validated = $request->validate([
            'label_template' => ['required', 'in:classic,modern,advance'],
            'default_print_format' => ['required', 'in:100x150,100x100,80x50,half-letter,letter'],
        ]);

        $brandOwner->update($validated);

        Audit::log('brand.design.updated', $brandOwner, 'Diseno de etiqueta actualizado.');

        return redirect()
            ->route('brand-settings.edit')
            ->with('status', 'Diseno de guia actualizado correctamente.');
    }

    private function brandOwner()
    {
        $user = Auth::user();

        if ($user->role === 'affiliate' && $user->affiliatedCompany) {
            return $user->affiliatedCompany()->with('tenant')->first();
        }

        return $user->tenant;
    }
}
