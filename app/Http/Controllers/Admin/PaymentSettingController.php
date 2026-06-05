<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        $settings = PaymentSetting::firstOrCreate([]);

        return view('admin.payment-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'account_owner' => 'nullable|string|max:255',
            'jawwal_pay_number' => [
                'required',
                'regex:/^[0-9]{10}$/'
            ],

            'palpay_number' => [
                'required',
                'regex:/^[0-9]{10}$/'
            ],
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'iban' => [
                'required',
                'string',
                'between:12,34',
                'regex:/^[A-Za-z0-9]+$/'
            ],
            'instructions' => 'nullable|string',
        ]);

        $settings = PaymentSetting::firstOrCreate([]);
        $settings->update($data);

        return redirect()
            ->route('admin.payment-settings.edit')
            ->with('success', 'Payment settings updated successfully.');
    }
}
