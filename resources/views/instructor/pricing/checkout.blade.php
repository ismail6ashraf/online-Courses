@extends('layouts.app')

@section('title', 'Checkout')
@section('page-title', 'Checkout')

@section('content')
    <div class="container py-5">
        <div class="row g-4 justify-content-center">

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h4 class="mb-3">Order Summary</h4>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Plan</span>
                            <strong>{{ $plan->name }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Duration</span>
                            <strong>{{ $plan->duration_days }} days</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Courses</span>
                            <strong>{{ $plan->max_courses ?? 'Unlimited' }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Students</span>
                            <strong>{{ $plan->max_students ?? 'Unlimited' }}</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between fs-4">
                            <span>Total</span>
                            <strong>
                                @if($plan->price == 0)
                                    FREE
                                @else
                                    ${{ number_format($plan->price, 2) }}
                                @endif
                            </strong>
                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            After sending the payment, upload the receipt.
                            Your subscription will be activated after admin approval.
                        </div>

                        @if($paymentSettings?->instructions)
                            <div class="alert alert-secondary mt-3 mb-0">
                                {{ $paymentSettings->instructions }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h4 class="mb-3">Local Payment</h4>

                        <div class="alert alert-warning">
                            Please transfer the exact amount, then upload the receipt image.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold">Jawwal Pay</h6>
                                    <p class="mb-1 small text-muted">Wallet Number</p>
                                    <div class="fw-bold">
                                        {{ $paymentSettings?->jawwal_pay_number ?? 'Not available' }}
                                    </div>

                                    <p class="mb-0 small text-muted mt-2">
                                        Name: {{ $paymentSettings?->account_owner ?? 'Not available' }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold">PalPay</h6>
                                    <p class="mb-1 small text-muted">Wallet Number</p>
                                    <div class="fw-bold">
                                        {{ $paymentSettings?->palpay_number ?? 'Not available' }}
                                    </div>

                                    <p class="mb-0 small text-muted mt-2">
                                        Name: {{ $paymentSettings?->account_owner ?? 'Not available' }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold">Bank Transfer</h6>

                                    <p class="mb-1 small text-muted">Account Name</p>
                                    <div class="fw-bold">
                                        {{ $paymentSettings?->account_owner ?? 'Not available' }}
                                    </div>

                                    <p class="mb-1 small text-muted mt-2">Bank</p>
                                    <div class="fw-bold">
                                        {{ $paymentSettings?->bank_name ?? 'Not available' }}
                                    </div>

                                    <p class="mb-1 small text-muted mt-2">Account Number</p>
                                    <div class="fw-bold text-break">
                                        {{ $paymentSettings?->bank_account_number ?? 'Not available' }}
                                    </div>

                                    <p class="mb-1 small text-muted mt-2">IBAN</p>
                                    <div class="fw-bold text-break">
                                        {{ $paymentSettings?->iban ?? 'Not available' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.checkout.pay', $plan) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Choose payment method</option>
                                    <option value="jawwal_pay" {{ old('payment_method') === 'jawwal_pay' ? 'selected' : '' }}>
                                        Jawwal Pay
                                    </option>
                                    <option value="palpay" {{ old('payment_method') === 'palpay' ? 'selected' : '' }}>
                                        PalPay
                                    </option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>
                                        Bank Transfer
                                    </option>
                                </select>

                                @error('payment_method')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sender Name</label>
                                <input type="text" name="sender_name" class="form-control"
                                    placeholder="Name used in transfer" value="{{ old('sender_name') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sender Phone / Account</label>
                                <input type="text" name="sender_account" class="form-control" placeholder="0591234567"
                                    required minlength="10" maxlength="10" pattern="[0-9]{10}"
                                    value="{{ old('sender_account') }}">

                                @error('sender_account')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Transaction Reference</label>
                                <input type="text" name="transaction_reference" class="form-control"
                                    placeholder="Transfer number or reference" value="{{ old('transaction_reference') }}">

                                @error('transaction_reference')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Receipt Image</label>
                                <input type="file" name="receipt_image" class="form-control" accept="image/*" required>

                                @error('receipt_image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-success w-100">
                                Submit Payment for Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
