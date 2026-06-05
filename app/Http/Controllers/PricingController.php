<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentSetting;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->get();

        $currentSubscription = auth()->user()->subscription;
        $currentPlanId = $currentSubscription?->plan_id;

        return view(
            'instructor.pricing.index',
            compact(
                'plans',
                'currentSubscription',
                'currentPlanId'
            )
        );
    }

    public function subscribe(Plan $plan)
    {
        $user = auth()->user();

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'start_date' => now(),
                'end_date' => now()->addDays($plan->duration_days),
                'status' => 'active',
            ]
        );

        return redirect()
            ->route('instructor.dashboard')
            ->with('success', 'Subscription activated successfully.');
    }



    public function checkout(Plan $plan)
    {
        $paymentSettings = PaymentSetting::first();

        return view('instructor.pricing.checkout', compact(
            'plan',
            'paymentSettings'
        ));
    }

    public function pay(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:bank_transfer,jawwal_pay,palpay',
            'transaction_reference' => 'nullable|string|max:255',
            'receipt_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('receipt_image')->store('receipts', 'public');

        Payment::create([
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'receipt_image' => $path,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('instructor.dashboard')
            ->with('success', 'Payment submitted successfully. Your subscription is pending admin approval.');
    }
}
