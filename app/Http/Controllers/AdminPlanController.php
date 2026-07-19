<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPlanController extends Controller
{
    public function plans(): \Illuminate\View\View
    {
        $this->authorize('access-admin');

        $plans = SubscriptionPlan::query()
            ->withCount('subscriptions')
            ->where('code', 'emprende')
            ->orderBy('monthly_price')
            ->get();

        return view('admin.plans', compact('plans'));
    }

    public function storePlan(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'in:emprende', 'unique:subscription_plans,code'],
            'name' => ['required', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        SubscriptionPlan::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'monthly_price' => (float) $validated['monthly_price'],
            'features' => $validated['features'] ?? [],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Plan creado correctamente.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'in:emprende', Rule::unique('subscription_plans', 'code')->ignore($plan->id)],
            'name' => ['required', 'string', 'max:120'],
            'monthly_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $plan->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'monthly_price' => (float) $validated['monthly_price'],
            'features' => $validated['features'] ?? [],
            'is_active' => true,
        ]);

        return back()->with('status', 'Plan actualizado correctamente.');
    }

    public function destroyPlan(SubscriptionPlan $plan): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('access-admin');

        if ($plan->code === 'emprende') {
            return back()->with('status', 'El plan Emprende es el plan unico y no se puede eliminar.');
        }

        if ($plan->subscriptions()->exists()) {
            return back()->with('status', 'No se puede eliminar: el plan tiene suscripciones asociadas.');
        }

        $plan->delete();

        return back()->with('status', 'Plan eliminado correctamente.');
    }
}
