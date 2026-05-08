@extends('admin::layouts.wrapper', [
    'activePage' => 'payments'
])

@section('title',  __('messages.payments'))

@php
    $timelineStep = function($payment) {
        // if payment status is paid
        if($payment->status == 'paid') {
            return 3;
        }

        // if gateway_id is set
        if($payment->gateway_id) {
            return 2;
        }

        return 1;
    };

    $timelineStep = $timelineStep($payment);
@endphp

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="{{ route('admin.payments.invoice-pdf', ['payment' => $payment->id]) }}" class="btn" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-download"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M19 18a3.5 3.5 0 0 0 0 -7h-1a5 4.5 0 0 0 -11 -2a4.6 4.4 0 0 0 -2.1 8.4" /><path d="M12 13l0 9" /><path d="M9 19l3 3l3 -3" /></svg>                Download Invoice PDF
            </a>
            @if($payment->isPaid())
            <button type="button" class="btn" data-bs-toggle="offcanvas" data-bs-target="#refundOrderDrawer" aria-controls="refundOrderDrawer">
                <!-- Download SVG icon from http://tabler.io/icons/icon/credit-card-refund -->
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-credit-card-refund"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 19h-6a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v4.5" /><path d="M3 10h18" /><path d="M7 15h.01" /><path d="M11 15h2" /><path d="M16 19h6" /><path d="M19 16l-3 3l3 3" /></svg>
                Refund
            </button>
            @endif
            <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#updatePaymentDrawer" aria-controls="updatePaymentDrawer">
                <!-- Download SVG icon from http://tabler.io/icons/icon/cash -->
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-cash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 15h-3a1 1 0 0 1 -1 -1v-8a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v3" /><path d="M7 9m0 1a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v8a1 1 0 0 1 -1 1h-12a1 1 0 0 1 -1 -1z" /><path d="M12 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /></svg>
                Update Payment
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-4">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ priceIn($payment->total(), $payment->currency), }}</h3>
                <div class="card-actions">
                    @if($payment->status == 'paid')
                        <span class="badge bg-green-lt">Paid</span>
                    @elseif(($payment->status == "unpaid"))
                        <span class="badge bg-danger-lt">Unpaid</span>
                    @else
                        <span class="badge bg-info-lt">{{ ucfirst($payment->status)  }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <tbody>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ priceIn($payment->subtotal, $payment->currency) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ priceIn($payment->tax, $payment->currency) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ priceIn($payment->discount, $payment->currency) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Total</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ priceIn($payment->total(), $payment->currency) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Earnings</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ priceIn($payment->earnings, $payment->currency) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($payment->taxDetails)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Tax Details</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <tbody>

                        @if($payment->taxDetails['company_name'])
                        <tr>
                            <td>Company</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ $payment->taxDetails['company_name'] }}
                            </td>
                        </tr>
                        @endif

                        @if($payment->taxDetails['tax_id'])
                        <tr>
                            <td>Tax ID</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ $payment->taxDetails['tax_id'] }}
                            </td>
                        </tr>
                        @endif

                        @if($payment->taxDetails['country'])
                        <tr>
                            <td>Country</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ \App\Facades\World::countryName($payment->taxDetails['country']) }}
                            </td>
                        </tr>
                        @endif

                        @if($payment->taxDetails['region'])
                        <tr>
                            <td>Region / State</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ $payment->taxDetails['region'] }}
                            </td>
                        </tr>
                        @endif

                        @if($payment->taxDetails['tax_name'])
                        <tr>
                            <td>Tax Name</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ $payment->taxDetails['tax_name'] }} {{ $payment->taxDetails['tax_rate'] ?? '0' }}%
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td>Tax Total</td>
                            <td class="text-secondary d-flex justify-content-end">
                                {{ price($payment->tax, in: $payment->currency) }}
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title">Payment History</h3>
                <ul class="steps steps-vertical">
                    <li class="step-item @if($timelineStep == 1) active @endif">
                        <div class="h4 m-0">Payment Created</div>
                        <div class="text-secondary">
                            The payment was generated in the system and is ready to be processed.
                        </div>
                        <div class="text-secondary">
                            {{ $payment->created_at->diffForHumans() }} ({{ $payment->created_at->format('d M Y') }})
                        </div>
                    </li>
                    <li class="step-item @if($timelineStep == 2) active @endif">
                        <div class="h4 m-0">Payment Method Selected</div>
                        <div class="text-secondary">
                            The payment method was selected by the user.
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="h4 m-0">Payment was successfully completed</div>
                        <div class="text-secondary">
                            The payment was successfully completed by the customer using {{ $payment->gatewayConfig ? $payment->gatewayConfig->display_name : 'Unknown Gateway' }}.
                        </div>
                    </li>
                    <li class="step-item @if($timelineStep == 3) active @endif">
                        <div class="h4 m-0">Finalized</div>
                        <div class="text-secondary">The payment was successfully processed and the customer received their items</div>
                        @if($payment->paid_at)
                        <div class="text-secondary">
                            {{ $payment->paid_at->diffForHumans() }} ({{ $payment->paid_at->format('d M Y') }})
                        </div>
                        @endif
                    </li>
                </ul>
            </div>
        </div>

        @livewire(admin_view_path('livewire.log'), ['model' => \App\Models\Payment::class, 'model_id' => $payment->id])
    </div>
    <div class="col-8">
        @if($payment->status == 'unpaid')
        <x-admin::alerts.action
            class="mb-3"
            variant="warning"
            title="Payment is not paid"
            :message="'This payment has not yet been paid by the user. Share the link below with the user to complete the payment.'"
        >
            <div class="btn-list mt-2">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#completePaymentManuallyModal">Complete Manually</button>
            </div>
        </x-admin::alerts.action>

        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title">Payment Link</h3>
                <p class="card-subtitle">
                    You can share this link with the user to complete the payment.
                </p>
                <div class="input-icon mb-3">
                    <input type="text" id="payment-url" value="{{ route('payments.view', ['payment' => $payment->token]) }}" class="form-control" placeholder="Search…" readonly="">
                    <span class="input-icon-addon">
                <!-- Download SVG icon from http://tabler.io/icons/icon/files -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 3v4a1 1 0 0 0 1 1h4"></path><path d="M18 17h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h4l5 5v7a2 2 0 0 1 -2 2z"></path><path d="M16 17v2a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h2"></path></svg>
                    </span>
                </div>
            </div>
        </div>
        @endif

        @if($payment->refunds->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Payment Refunds ({{ $payment->refunds->count() }})</h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordion-default">

                        @foreach($payment->refunds()->latest()->get() as $refund)
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $refund->id }}-default" aria-expanded="false">
                                        Payment Refund #{{ $refund->id }} - {{ priceIn($refund->amount, $refund->currency) }} - {{ $refund->created_at->format('Y-m-d H:i:s') }}
                                    </button>
                                </div>
                                <div id="collapse-{{ $refund->id }}-default" class="accordion-collapse collapse" data-bs-parent="#accordion-default">
                                    <div class="accordion-body">
                                        <div>
                                            <h4>Refunded By</h4>
                                            <div class="row align-items-center">
                                                @if($refund->user)
                                                <div class="col">
                                                    <div class="card-title"><a href="{{ route('admin.users.edit', $refund->user->id) }}" class="text-reset" wire:navigate="">{{ $refund->user->fullname }}</a></div>
                                                    <div class="card-subtitle"><a href="{{ route('admin.users.edit', $refund->user->id) }}" class="text-reset" wire:navigate="">{{ $refund->user->username }} ({{ $refund->user->email }})</a></div>
                                                </div>
                                                @else
                                                    <div class="col">
                                                        <div class="card-title">Deleted User</div>
                                                        <div class="card-subtitle">Refunded by a deleted user</div>
                                                    </div>
                                                @endif
                                            </div>
                                            <h4>Refunded to</h4>
                                            <div class="row align-items-center">
                                                @if($refund->gatewayConfig)
                                                    <div class="col">
                                                        <div class="card-title"><a href="{{ route('admin.gateways.configs.edit', $refund->gatewayConfig->id) }}" class="text-reset" wire:navigate="">{{ $refund->gatewayConfig->display_name }}</a></div>
                                                    </div>
                                                @else
                                                    <div class="col">
                                                        <div class="card-title">Deleted Gateway</div>
                                                        <div class="card-subtitle">Refunded to a deleted gateway</div>
                                                    </div>
                                                @endif
                                            </div>
                                            <h4>Amount</h4>
                                            <div>
                                                <pre>{{ priceIn($refund->amount, $refund->currency) }} {{ $refund->currency }}</pre>
                                            </div>
                                            <h4>Transaction ID</h4>
                                            <div>
                                                <pre>{{ $refund->transaction_id }}</pre>
                                            </div>
                                            <h4>Reason</h4>
                                            <div>
                                                <pre>{{ $refund->reason ? : 'No reason provided' }}</pre>
                                            </div>
                                            <h4>Date</h4>
                                            <div>
                                                <pre>{{ $refund->created_at->format('Y-m-d H:i:s') }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        @endif

        <div class="mb-3">
            @livewire(admin_view_path('payments.livewire.payment-info-card'), ['payment' => $payment])
        </div>

        @if($payment->user)
            <div class="mb-3">
                @livewire(admin_view_path('users.livewire.user-info-card'), ['user' => $payment->user])
            </div>
        @endif

        @if($payment->webhooks->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payment Webhooks ({{ $payment->webhooks->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordion-default">

                    @foreach($payment->webhooks()->latest()->get() as $webhook)
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $webhook->id }}-default" aria-expanded="false">
                                    Payment Webhook #{{ $webhook->id }} - {{ $webhook->message }} - {{ $webhook->created_at->format('Y-m-d H:i:s') }}
                                </button>
                            </div>
                            <div id="collapse-{{ $webhook->id }}-default" class="accordion-collapse collapse" data-bs-parent="#accordion-default">
                                <div class="accordion-body">
                                    <div>
                                        <h4>Message</h4>
                                        <div>
                                            <pre><code>{{ $webhook->message }}</code></pre>
                                        </div>
                                        <h4>Date</h4>
                                        <div>
                                            <pre>{{ $webhook->created_at->format('Y-m-d H:i:s') }}</pre>
                                        </div>
                                        @if($webhook->ip_address)
                                            <h4>IP Address</h4>
                                            <div>
                                                <pre>{{ $webhook->ip_address }}</pre>
                                            </div>
                                        @endif
                                        @if($webhook->headers)
                                            <h4>Headers</h4>
                                            <div>
                                                <pre><code>{{ json_encode($webhook->headers, JSON_PRETTY_PRINT) }}</code></pre>
                                            </div>
                                        @endif
                                        @if($webhook->payload)
                                            <h4>Payload</h4>
                                            <div>
                                                <pre><code>{{ json_encode($webhook->payload, JSON_PRETTY_PRINT) }}</code></pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if(!$payment->isPaid())
    @livewire(admin_view_path('payments.livewire.complete-payment-manually-modal'), ['payment' => $payment])
@endif

@livewire(admin_view_path('payments.livewire.refund-payment-drawer'), ['payment' => $payment])
@livewire(admin_view_path('payments.livewire.update-payment-drawer'), ['payment' => $payment])

@endsection
