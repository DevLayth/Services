<div>
    <h1 class="mb-4">Journal Entries</h1>

    {{-- Journal Entries Table Card --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>ref</th>
                        <th>Description</th>
                        <th>Invoice-Table</th>
                        <th>Created_at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-light align-middle">
                    @foreach ($journalEntries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->reference }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ $entry->invoice_id }}-{{ $entry->invoice_table_name }} </td>
                            <td>{{ $entry->created_at }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#journalEntryLinesModal"
                                    wire:click="loadJournalEntryLines({{ $entry->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <!-- Modern Journal Entry Lines Modal -->
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                    <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>

            </div>
        </div>
    </div>


</div>
