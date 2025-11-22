<div>
        <h1 class="mb-4">Paid Invoices</h1>

    {{-- Invoices Table Card --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-uppercase small text-muted">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Invoice Date</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $inv->id }}</td>
                                <td>{{ $inv->customer_name }}</td>
                                <td>{{ $inv->service_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($inv->paid_at)->format('d M Y') }}</td>
                                <td class="fw-bold">
                                    {{ number_format($inv->amount, 2) }} {{ $inv->currency_code }}
                                </td>
                                <td class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary shadow-sm"
                                        wire:click="$set('selectedInvoiceId', @json($inv->id))"
                                        data-bs-toggle="modal" data-bs-target="#invoiceModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger shadow-sm"
                                        wire:click="$set('selectedInvoiceId', @json($inv->id))"
                                        data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- Invoice Modal --}}
<div wire:ignore.self class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white py-3">
                <div class="d-flex align-items-center w-100 justify-content-between">
                    <div class="d-flex align-items-center">

                        <h5 class="modal-title fw-bold"><i class="bi bi-receipt"></i> Invoice</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="font-family: 'Arial', sans-serif;">
                @if ($selectedInvoiceId)
                    @php
                        $invoice = collect($invoices)->where('id', $selectedInvoiceId)->first();
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-0">Shahan</h3>
                            <small class="text-muted">Shahan Tower, Duhok, Kurdistan Region, Iraq</small>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-0">Invoice #{{ $invoice->id }}</h5>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</small>
                        </div>
                    </div>
                    <hr>

                    {{-- Customer Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-muted">Bill To:</h6>
                            <p class="fw-bold mb-1">{{ $invoice->customer_name }}</p>
                            <small class="text-muted">{{ $invoice->customer_phone ?? '' }}</small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="fw-semibold text-muted">Service:</h6>
                            <p class="fw-bold mb-1">{{ $invoice->service_name }}</p>
                            <small class="text-muted">Duration: {{ $invoice->paid_from }} - {{ $invoice->paid_to }}</small>
                        </div>
                    </div>

                    {{-- Invoice Items Table --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th>Item / Description</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Discount %</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $invoice->service_name }}</td>
                                    <td class="text-end">{{ number_format($invoice->amount_one_month, 2) }} {{ $invoice->currency_code }}</td>
                                    <td class="text-end">{{ $invoice->discount }}</td>
                                    <td class="text-end fw-bold">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency_code }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal</th>
                                    <th class="text-end">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency_code }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Discount</th>
                                    <th class="text-end">{{ $invoice->discount }}%</th>
                                </tr>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end fw-bold text-success">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency_code }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Footer Note --}}
                    <div class="text-center mt-4">
                        <small class="text-muted">Thank you for your business!</small>
                    </div>

                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-x display-4"></i>
                        <p class="mt-3">No invoice selected.</p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-warning px-4" onclick="window.print();">
                   <i class="bi bi-printer"></i> Print
                </button>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #invoiceModal, #invoiceModal * {
        visibility: visible;
    }

    #invoiceModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;

    }

    #invoiceModal .modal-header,
    #invoiceModal .modal-footer {
        display: none;
    }
}
</style>

 <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                 @if ($selectedInvoiceId)
                    @php
                        $invoice = collect($invoices)->where('id', $selectedInvoiceId)->first();
                    @endphp
                <div class="modal-body text-center py-4 fs-5">
                    <strong class="text-danger">Are you sure?</strong>
                    <p class="text-muted">You are about to delete:<br> {{ $invoice->customer_name }} invoice at {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</p>
                </div>
                @else
                <div class="modal-body text-center py-4 fs-5">
                    <strong class="text-danger">No invoice selected.</strong>
                </div>
                @endif
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                        >Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteInvoice"
                        wire:loading.attr="disabled" data-bs-dismiss="modal">Yes, Delete</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Alert Messages -->
    @if ($showMessage)
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080; min-width: 250px;">
            <div class="alert alert-{{ $messageType }} d-flex align-items-center justify-content-between shadow-sm rounded-3 p-2 mb-2"
                role="alert">
                <div class="d-flex align-items-center">
                    <svg class="bi flex-shrink-0 me-2" width="20" height="20" role="img"
                        aria-label="{{ $messageType == 'success' ? 'Success:' : 'Error:' }}">
                        <use
                            xlink:href="#{{ $messageType == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' }}" />
                    </svg>
                    <div class="small">{{ $message }}</div>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

      {{-- Bootstrap Icons --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.07 0L13.03 6l-1.06-1.06L7 9.94 4.97 7.91 3.91 9l3.06 3.03z" />
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M8 0c-.69 0-1.34.36-1.66.92L.165 14.235c-.312.564.027 1.265.66 1.265h14.35c.633 0 .972-.701.66-1.265L9.66.92A1.75 1.75 0 0 0 8 0zm.93 11.412a.625.625 0 1 1-1.25 0 .625.625 0 0 1 1.25 0zm-.93-7.25c.335 0 .625.28.625.625v4.5a.625.625 0 0 1-1.25 0v-4.5c0-.345.29-.625.625-.625z" />
        </symbol>
    </svg>

</div>
