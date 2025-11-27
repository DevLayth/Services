<div>

    <!-- HEADER -->
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-card-checklist fs-1 text-black"></i>
        <h2 class="mb-0 ms-3">Subscriptions</h2>
    </div>

    <!-- SUBSCRIPTIONS TABLE -->
    <div class="card shadow-sm rounded-3 p-4">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Price</th>
                        <th>Currency</th>
                        <th>Start Date</th>
                        <th>Next Payment Date</th>
                        <th>End Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>{{ $sub->id }}</td>
                            <td>{{ $sub->customer_name }}</td>
                            <td>{{ $sub->service_name }}</td>
                            <td>{{ number_format($sub->price, 2) }}</td>
                            <td>{{ $sub->currency_code ?? 'N/A' }}</td>
                            <td>{{ $sub->start_date }}</td>
                            <td>{{ $sub->next_payment_date }}</td>
                            <td>{{ $sub->end_date ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if (!$sub->end_date || $sub->end_date > now())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary me-1"
                                    wire:click="generateInvoices({{ $sub->id }})" data-bs-toggle="modal"
                                    data-bs-target="#invoiceModal">
                                    <i class="bi bi-eye"></i>
                                </button>


                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                No subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- INVOICE MODAL -->
    <div wire:ignore.self class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoices</h5>
                    <button type="button" wire:click="resetInputs" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if ($invoicesCount > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $inv)
                                    <tr>
                                        <td>{{ $inv['invoice_month'] }}</td>
                                        <td>{{ $inv['invoice_date'] }}</td>
                                        <td>{{ $inv['customer_name'] }}</td>
                                        <td>{{ $inv['service_name'] }}</td>
                                        <td>{{ number_format($inv['price'], 2) }} {{ $inv['currency_code'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">No invoices found.</p>
                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="resetInputs" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal"
                    wire:click="initPaymentCurrency({{ $selectedSubscriptionId }})"
                        data-bs-target="#paymentModal">
                        Process Payment
                    </button>
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

    <!-- PAYMENT MODAL -->
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Months to Pay</label>
                        <input type="number" class="form-control" wire:model.live="paymentMonths" min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" class="form-control" wire:model.live="discount" min="0"
                            max="100">
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Payment Currency</label>
                        <select class="form-select" wire:model.live="paymentCurrency">
                            <option value="">Select</option>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccountId')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control" wire:model.live="finalAmount" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" wire:model="paymentMethod">
                            <option value="">Select</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->account_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccountId')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment for</label>
                        <select class="form-select" wire:model="selectedAccountId">
                            <option value="">Select</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->account_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedAccountId')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetInputs">Close</button>
                    <button class="btn btn-primary" wire:click="pay">Submit Payment</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
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
