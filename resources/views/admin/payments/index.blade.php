@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent">
        <h5 class="mb-0">Payments</h5>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->user->name }}</td>
                            <td>{{ $payment->plan->name }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                            <td>{{ $payment->transaction_reference ?? '—' }}</td>
                            <td>
                                @if($payment->receipt_image)
                                    <a href="{{ asset('storage/' . $payment->receipt_image) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->status === 'pending')
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
