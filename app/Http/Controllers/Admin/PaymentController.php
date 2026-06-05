<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'plan'])
            ->latest()
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function approve(Payment $payment)
    {
        $payment->update([
            'status' => 'approved',
        ]);

        Subscription::updateOrCreate(
            ['user_id' => $payment->user_id],
            [
                'plan_id' => $payment->plan_id,
                'start_date' => now(),
                'end_date' => now()->addDays($payment->plan->duration_days),
                'status' => 'active',
            ]
        );

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment approved and subscription activated.');
    }

    public function reject(Payment $payment)
    {
        $payment->update([
            'status' => 'rejected',
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected.');
    }
}
