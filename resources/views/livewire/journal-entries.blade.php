<div>
    <h1 class="mb-4">Journal Entries</h1>

    <!-- Filters Button -->
    <button type="button" class="btn btn-secondary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="bi bi-funnel"></i> Filters
        <span class="badge bg-light text-dark ms-1">{{ $this->activeFiltersCount }}</span>
    </button>
<button class="btn btn-warning btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="bi bi-box-arrow-up"></i> Export
        <span class="badge bg-light text-dark ms-1"></span>
    </button>
    <!-- Journal Entries Table Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Invoice-Table</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-light align-middle">
                    @forelse ($journalEntries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->invoice_id }} - {{ $entry->invoice_table_name }}</td>
                            <td>{{ number_format($entry->amount, 2) }}</td>
                            <td>{{ $currencies[$entry->currency_id] ?? '' }}</td>
                            <td>{{ $entry->created_at }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#journalEntryLinesModal"
                                    wire:click="loadJournalEntryLines({{ $entry->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#notesModal"
                                    wire:click="setSelectedJournalEntryId({{ $entry->id }})">
                                    <i class="bi bi-journal-minus"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                No Journal Entries Found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Info -->
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap px-3">
            <small>
                Page {{ $rowCount ? $currentPage : 0 }} of {{ $totalPages }} |
                Total rows: {{ $rowCount }}
            </small>
        </div>

        <!-- Pagination -->
        @if ($totalPages > 1)
            <nav aria-label="Journal pagination" class="mt-2">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                        <button class="page-link" wire:click="goToPage({{ $currentPage - 1 }})">&laquo;</button>
                    </li>

                    @php
                        $maxPagesToShow = 10;
                        $start = max(1, $currentPage - floor($maxPagesToShow / 2));
                        $end = min($totalPages, $start + $maxPagesToShow - 1);
                        $start = max(1, $end - $maxPagesToShow + 1);
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                            <button class="page-link" wire:click="goToPage({{ $i }})">{{ $i }}</button>
                        </li>
                    @endfor

                    <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                        <button class="page-link" wire:click="goToPage({{ $currentPage + 1 }})">&raquo;</button>
                    </li>
                </ul>
            </nav>
        @endif
    </div>

    {{-- ================= FILTER MODAL ================= --}}
    <div wire:ignore.self class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <!-- Header -->
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-funnel"></i> Filter Journal Entries</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- invoice_id --}}
                        <div class="col-md-6">
                            <label class="form-label small text-muted"><i class="bi bi-receipt"></i> Invoice ID</label>
                            <input type="number" class="form-control" wire:model.live="filter.invoice_id" min="1">
                            @if ($filter['invoice_id'])
                                <span class="badge bg-secondary mt-1">{{ $filter['invoice_id'] }}</span>
                            @endif
                        </div>

                        {{-- currency --}}
                        <div class="col-md-6">
                            <label class="form-label small text-muted"><i class="bi bi-currency-dollar"></i> Currency</label>
                            <select class="form-select" wire:model.live="filter.currency">
                                <option value="">All</option>
                                @foreach ($currencies as $id => $code)
                                    <option value="{{ $id }}">{{ $code }}</option>
                                @endforeach
                            </select>
                            @if ($filter['currency'])
                                <span class="badge bg-secondary mt-1">{{ $currencies[$filter['currency']] ?? '' }}</span>
                            @endif
                        </div>

                        @foreach (['from' => 'Date From', 'to' => 'Date To'] as $field => $label)
                            <div class="col-md-6">
                                <label class="form-label small text-muted"><i class="bi bi-calendar"></i> {{ $label }}</label>
                                <input type="date" class="form-control" wire:model.live="filter.{{ $field }}">
                                @if ($filter[$field])
                                    <span class="badge bg-success mt-1">{{ $filter[$field] }}</span>
                                @endif
                            </div>
                        @endforeach

                        @foreach (['amount_from' => 'Min Amount', 'amount_to' => 'Max Amount'] as $field => $label)
                            <div class="col-md-6">
                                <label class="form-label small text-muted"><i class="bi bi-currency-dollar"></i> {{ $label }}</label>
                                <input type="number" class="form-control" wire:model.live="filter.{{ $field }}" min="0" step="0.01">
                                @if ($filter[$field])
                                    <span class="badge bg-secondary mt-1">{{ $filter[$field] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="resetFilters">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" wire:click="loadJournalEntries">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>
        </div>
    </div>



    {{-- ================= Export MODAL ================= --}}
    <div wire:ignore.self class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-up"></i> Export Journal Entries</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Select the format to export your journal entries:</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success w-50" wire:click="exportExcel"><i class="bi bi-file-earmark-excel"></i>
                            Excel</button>
                        <button class="btn btn-outline-danger w-50" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf"></i>
                            PDF</button>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    {{-- ================= NOTES MODAL ================= --}}
    <div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-journal-text"></i> Journal Entry Notes</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @php
                        $entry = collect($journalEntries)->firstWhere('id', $selectedJournalEntryId);
                        $entry = $entry ? (object) $entry : null;
                    @endphp

                    @if ($entry && $entry->note)
                        <div class="card bg-light p-3" style="max-height: 300px; overflow:auto;">
                            <p class="mb-0">{{ $entry->note }}</p>
                        </div>
                    @else
                        <div class="text-muted text-center py-4">
                            <i class="bi bi-journal-x fs-1 mb-2 d-block"></i>
                            <p class="mb-0">No notes available for this journal entry.</p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= JOURNAL ENTRY LINES MODAL ================= --}}
    <div wire:ignore.self class="modal fade" id="journalEntryLinesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header px-4 py-3 border-0 bg-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="bi bi-journal-text me-2"></i>
                        Journal Entry "{{ $selectedJournalEntryId }}" Lines
                    </h5>
                    <button type="button" wire:click="resetInput" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark border-bottom">
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($journalEntryLines as $line)
                                        <tr>
                                            <td>{{ $line->id }}</td>
                                            <td>{{ $line->account_name }}</td>
                                            <td class="text-success">{{ number_format($line->debit, 2) }}</td>
                                            <td class="text-danger">{{ number_format($line->credit, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-warning fw-bold">
                                        <td colspan="2" class="text-center">Total</td>
                                        <td class="text-success">{{ number_format(collect($journalEntryLines)->sum('debit'), 2) }}</td>
                                        <td class="text-danger">{{ number_format(collect($journalEntryLines)->sum('credit'), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" wire:click="resetInput" class="btn btn-dark px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
