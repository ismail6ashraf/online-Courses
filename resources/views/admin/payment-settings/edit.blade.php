@extends('layouts.app')

@section('title', 'Payment Settings')
@section('page-title', 'Payment Settings')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold">
                        <i class="bi bi-wallet2 me-2"></i>
                        Payment Settings
                    </h5>
                    <p class="text-muted small mb-0">
                        These details will appear on the instructor checkout page.
                    </p>
                </div>

                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.payment-settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Account Owner</label>
                            <input type="text" name="account_owner" class="form-control"
                                value="{{ old('account_owner', $settings->account_owner) }}"
                                placeholder="Example: Ismail Ashraf">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jawwal Pay Number</label>
                                <input type="text" name="jawwal_pay_number" class="form-control"
                                    value="{{ old('jawwal_pay_number', $settings->jawwal_pay_number) }}"
                                    placeholder="059xxxxxxx">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">PalPay Number</label>
                                <input type="text" name="palpay_number" class="form-control"
                                    value="{{ old('palpay_number', $settings->palpay_number) }}" placeholder="059xxxxxxx">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control"
                                    value="{{ old('bank_name', $settings->bank_name) }}" placeholder="Bank of Palestine">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control"
                                    value="{{ old('bank_account_number', $settings->bank_account_number) }}"
                                    placeholder="Account number">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">IBAN</label>
                                <input type="text" name="iban" class="form-control"
                                    value="{{ old('iban', $settings->iban) }}" placeholder="PSxxxxxxxxxxxxxxxx">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-semibold">Payment Instructions</label>
                            <textarea name="instructions" rows="4" class="form-control"
                                placeholder="Write instructions shown to instructors before uploading payment receipt.">{{ old('instructions', $settings->instructions) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Checkout Preview
                    </h6>

                    <div class="border rounded p-3 mb-3">
                        <div class="small text-muted">Account Owner</div>
                        <div class="fw-semibold">{{ $settings->account_owner ?? 'Not set' }}</div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="small text-muted">Jawwal Pay</div>
                        <div class="fw-semibold">{{ $settings->jawwal_pay_number ?? 'Not set' }}</div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="small text-muted">PalPay</div>
                        <div class="fw-semibold">{{ $settings->palpay_number ?? 'Not set' }}</div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="small text-muted">Bank</div>
                        <div class="fw-semibold">{{ $settings->bank_name ?? 'Not set' }}</div>
                        <div class="small text-muted mt-2">IBAN</div>
                        <div class="fw-semibold text-break">{{ $settings->iban ?? 'Not set' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
