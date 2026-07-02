<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BoldPaymentLinkService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->where('code', 'emprende')
            ->where('code', '!=', 'fundador')
            ->orderBy('monthly_price')
            ->get();

        return view('auth.register', compact('plans'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_phone' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')
                    ->where('code', 'emprende')
                    ->where('is_active', true),
            ],
            'start_mode' => ['required', 'in:trial_guides,pay_now'],
        ]);

        $phoneAlreadyUsed = Tenant::query()
            ->where('brand_whatsapp', $validated['business_phone'])
            ->orWhere('phone', $validated['business_phone'])
            ->exists();

        if ($validated['start_mode'] === 'trial_guides' && $phoneAlreadyUsed) {
            throw ValidationException::withMessages([
                'business_phone' => 'Este WhatsApp ya fue usado para una prueba gratis. Puedes crear la cuenta eligiendo pagar ahora.',
            ]);
        }

        [$user, $subscription] = DB::transaction(function () use ($validated) {
            $baseSubdomain = Str::slug($validated['business_name']);
            $subdomain = $baseSubdomain ?: 'negocio';
            $suffix = 2;

            while (Tenant::query()->where('subdomain', $subdomain)->exists()) {
                $subdomain = $baseSubdomain.'-'.$suffix;
                $suffix++;
            }

            $tenant = Tenant::query()->create([
                'name' => $validated['business_name'],
                'legal_name' => $validated['business_name'],
                'email' => $validated['email'],
                'phone' => $validated['business_phone'] ?? null,
                'subdomain' => $subdomain,
                'status' => 'active',
                'brand_color' => '#0047D9',
                'brand_whatsapp' => $validated['business_phone'] ?? null,
                'brand_message' => 'Gracias por tu compra.',
            ]);

            $subscription = $tenant->subscriptions()->create([
                'subscription_plan_id' => $validated['subscription_plan_id'],
                'status' => 'active',
                'starts_at' => today(),
                'ends_at' => null,
                'start_mode' => $validated['start_mode'] === 'trial_guides' ? 'trial_guides' : 'paid',
                'trial_guide_limit' => $validated['start_mode'] === 'trial_guides' ? 10 : 0,
                'trial_guide_used' => 0,
                'next_payment_at' => $validated['start_mode'] === 'trial_guides' ? null : today(),
                'notes' => $validated['start_mode'] === 'trial_guides'
                    ? 'Registro publico con 10 guias gratis.'
                    : 'Registro publico con pago inicial pendiente.',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'tenant_admin',
                'status' => 'active',
            ]);

            return [$user, $subscription];
        });

        event(new Registered($user));

        Auth::login($user);

        if ($validated['start_mode'] === 'pay_now') {
            $this->tryCreateInitialPaymentLink($subscription);

            return redirect()
                ->route('billing.checkout')
                ->with('status', 'Tu cuenta fue creada. Completa el primer pago para dejar tu mensualidad al dia.');
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Tus 10 guias gratis estan activas.');
    }

    private function tryCreateInitialPaymentLink($subscription): void
    {
        if (! config('services.bold.api_key')) {
            return;
        }

        $subscription->load(['tenant', 'plan']);

        $payment = SubscriptionPayment::query()->create([
            'tenant_id' => $subscription->tenant_id,
            'tenant_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'provider' => 'bold',
            'reference' => 'TE-FIRST-'.$subscription->tenant_id.'-'.now()->format('YmdHis'),
            'status' => 'pending',
            'amount' => $subscription->plan->monthly_price,
            'currency' => 'COP',
        ]);

        try {
            app(BoldPaymentLinkService::class)->createLink($payment);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'provider_payload' => ['error' => $exception->getMessage()],
            ]);
        }
    }
}
