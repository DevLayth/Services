<div>
    <h1 class="mb-4">Journal Entries</h1>

    {{-- Journal Entries Table Card --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Invoice-Table</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Created_at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-light align-middle">
                    @foreach ($journalEntries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->invoice_id }}-{{ $entry->invoice_table_name }} </td>
                            <td>{{ $entry->amount }}</td>
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
                                    wire:click="setSelectedJournalEntry({{ $entry->id }})">
                                    <i class="bi bi-journal-minus"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @empty($journalEntries)
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            No Journal Entries Found.
                        </td>
                    </tr>
                @endempty
            </table>
        </div>
    </div>


<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg border-0">

            <!-- Header -->
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title d-flex align-items-center gap-2" id="notesModalLabel">
                    <i class="bi bi-journal-text fs-4"></i>
                    <span class="fw-semibold">Journal Entry Notes</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                @php
                    $entry = collect($journalEntries)->firstWhere('id', $selectedJournalEntryId);
                    $entry = $entry ? (object) $entry : null;
                @endphp

                @if ($entry && $entry->note)
                    <div class="card bg-light text-start overflow-auto" style="max-height: 300px;">
                        <div class="card-body">
                            <p class="mb-0">{{ $entry->note }}</p>
                        </div>
                    </div>
                @else
                    <div class="text-muted">
                        <i class="bi bi-journal-x fs-1 mb-2 d-block"></i>
                        <p class="mb-0">No notes available for this journal entry.</p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>



    <!-- Journal Entry Lines Modal -->
    <div wire:ignore.self class="modal fade" id="journalEntryLinesModal" tabindex="-1"
        aria-labelledby="journalEntryLinesLabel" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <!-- Header -->
                <div class="modal-header px-4 py-3 border-0 bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="journalEntryLinesLabel">
                        <i class="bi bi-journal-text me-2"></i>
                        Journal Entry "{{ $selectedJournalEntryId }}" Lines
                    </h5>
                    <button type="button" wire:click="resetInput" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body px-4 py-4">

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">

                            <!-- Table -->
                            <table class="table table-modern table-hover mb-0">
                                <thead class="table-dark border-bottom">
                                    <tr class="text-secondary text-uppercase small fw-bold">
                                        <th class="py-3 ps-4">#</th>
                                        <th class="py-3">Account</th>
                                        <th class="py-3">Debit</th>
                                        <th class="py-3">Credit</th>
                                    </tr>
                                </thead>

                                <tbody class="align-middle">
                                    @foreach ($journalEntryLines as $line)
                                        <tr class="border-bottom">
                                            <td class="ps-4 fw-semibold text-dark">{{ $line->id }}</td>
                                            <td>{{ $line->account_name }}</td>
                                            <td class="text-success fw-semibold">
                                                {{ number_format($line->debit, 2) }}
                                            </td>
                                            <td class="text-danger fw-semibold">
                                                {{ number_format($line->credit, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="table-warning border-top fw-bold">

                                        <td colspan="2" class="fw-bold text-secondary text-center pe-4">
                                            Total
                                        </td>
                                        <td class="text-success fw-bold">
                                            {{ number_format(collect($journalEntryLines)->sum('debit'), 2) }}
                                        </td>
                                        <td class="text-danger fw-bold">
                                            {{ number_format(collect($journalEntryLines)->sum('credit'), 2) }}
                                        </td>
                                    </tr>


                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" wire:click="resetInput" class="btn btn-dark px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>

            </div>
        </div>
    </div>


</div>
