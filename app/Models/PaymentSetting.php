<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'account_owner',
        'jawwal_pay_number',
        'palpay_number',
        'bank_name',
        'bank_account_number',
        'iban',
        'instructions',
    ];
}
