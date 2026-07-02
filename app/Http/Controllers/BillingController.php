<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\BoldPaymentLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function checkout(): View
    {
        $tenant = Auth::user()->tenant;
        abort_unless($tenant, 404);

        $subscription = $tenant->currentSubscription()->with('plan')->first();
        abort_unless($subscription, 404);

        $lastPayment = $tenant->subscriptionPayments()
            ->where('tenant_subscription_id', $subscription->id)
            ->latest()
            ->first();

        return view('billing.checkout', compact('tenant', 'subscription', 'lastPayment'));
    }

    public function createPaymentLink(BoldPaymentLinkService $bold): RedirectResponse
    {
        $tenant = Auth::user()->tenant;
        abort_unless($tenant, 404);

        $subscription = $tenant->currentSubscription()->with('plan')->first();
        abort_unless($subscription && $subscription->plan, 404);

        $payment = SubscriptionPayment::query()->create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'provider' => 'bold',
            'reference' => 'TE-CHECKOUT-'.$tenant->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'status' => 'pending',
            'amount' => $subscription->plan->monthly_price,
            'currency' => 'COP',
        ]);

        try {
            $bold->createLink($payment);
        } catch (Throwable $exception) {
            $payment->update([
                'status' => 'failed',
                'provider_payload' => ['error' => $exception->getMessage()],
            ]);

            return back()->with('status', $exception->getMessage());
        }

        return redirect()
            ->route('billing.checkout')
            ->with('status', 'Link de pago generado.');
    }
}