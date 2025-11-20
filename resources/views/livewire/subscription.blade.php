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
                                @if(!$sub->end_date || $sub->end_date > now())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info me-1"
                                        wire:click="generateInvoices({{ $sub->id }})"
                                        data-bs-toggle="modal"
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if($invoicesCount > 0)
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
                                @foreach($invoices as $inv)
                                    <tr>
                                        <td>{{ $inv['invoice_month'] }}</td>
                                        <td>{{ $inv['invoice_date'] }}</td>
                                        <td>{{ $inv['customer_name'] }}</td>
                                        <td>{{ $inv['service_name'] }}</td>
                                        <td>{{ number_format($inv['price'],2) }} {{ $inv['currency_code'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">No invoices found.</p>
                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" data-bs-dismiss="modal"
                            data-bs-toggle="modal"
                            data-bs-target="#paymentModal">
                        Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Months to Pay</label>
                        <input type="number" class="form-control"
                               wire:model.live="paymentMonths"
                               min="1"
                               max="{{ $invoicesCount }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" class="form-control"
                               wire:model.live="discount"
                               min="0"
                               max="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="text" class="form-control"
                               value="{{ number_format($finalAmount ?? 0,2) }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" wire:model="paymentMethod">
                            <option value="">Select</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetInputs">Close</button>
                    <button class="btn btn-primary" wire:click="pay">Submit Payment</button>
                </div>

            </div>
        </div>
    </div>

</div>
