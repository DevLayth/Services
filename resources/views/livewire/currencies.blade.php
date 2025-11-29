<div>
    <!-- Header -->
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center my-4 py-2 px-2 border-bottom">
            <div class="d-flex align-items-center">
                <i class="bi bi-currency-exchange fs-1 text-black"></i>
                <h2 class="ms-3">Currencies</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#currencyModal">
                <i class="bi bi-plus-lg"></i> Add Currency
            </button>
        </div>
    </div>
    <!-- Currencies Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Symbol</th>
                    <th>Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($currencies as $currency)
                    <tr>
                        <td>{{ $currency->name }}</td>
                        <td>{{ $currency->code }}</td>
                        <td>{{ $currency->symbol }}</td>
                        <td>{{ $currency->rate }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-2" wire:click="editCurrency({{ $currency->id }})"
                                data-bs-toggle="modal" data-bs-target="#currencyModal">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger"
                                wire:click="setCurrencyId({{ $currency->id }}, '{{ $currency->name }}')"
                                data-bs-toggle="modal" data-bs-target="#deleteCurrencyModal">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
                @empty($currencies)
                    <tr>
                        <td colspan="5" class="text-center text-muted">No currencies found.</td>
                    </tr>
                @endempty
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Currency Modal -->
    <div wire:ignore.self class="modal fade" id="currencyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editMode ? 'Edit Currency' : 'Add New Currency' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInput"></button>
                </div>
                <form wire:submit.prevent="{{ $editMode ? 'updateCurrency' : 'addCurrency' }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label @error('name') text-danger @enderror">Name</label>
                            <input type="text" class="form-control" wire:model.lazy="name" autofocus>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label @error('code') text-danger @enderror">Code</label>
                            <input type="text" class="form-control" wire:model.lazy="code">
                            @error('code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label @error('symbol') text-danger @enderror">Symbol</label>
                            <input type="text" class="form-control" wire:model.lazy="symbol">
                            @error('symbol')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label @error('rate') text-danger @enderror">Rate</label>
                            <input type="number" step="0.0001" class="form-control" wire:model.lazy="rate">
                            @error('rate')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetInput">Close</button>
                        <button type="button" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:click="{{ $editMode ? 'updateCurrency' : 'addCurrency' }}"
                            @if ($editMode) data-bs-dismiss="modal" @endif>
                            {{ $editMode ? 'Update Currency' : 'Save Currency' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteCurrencyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetInput"> </button>
                </div>
                <div class="modal-body text-center py-4 fs-5">
                    <strong class="text-danger">Are you sure?</strong>
                    <p class="text-muted">You are about to delete: {{ $name }}</p>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" wire:click="resetInput"> Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteCurrency"
                        wire:loading.attr="disabled" data-bs-dismiss="modal">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Alerts -->
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
