<div class="container py-4">

    {{-- Header with Add Account Button --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="flex-grow-1">Accounts</h2>
        <button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            Add New Account
        </button>
    </div>

    {{-- Accounts Table --}}
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->id }}</td>
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->account_type }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#editAccountModal"
                                wire:click="$set('selectedAccountId', {{ $account->id }})">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteAccountModal"
                                wire:click="$set('selectedAccountId', {{ $account->id }})">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>

            @empty($accounts)
                <tr>
                    <td colspan="4" class="text-center text-muted">No accounts found.</td>
                </tr>
            @endempty
        </table>
    </div>

    {{-- Add Account Modal --}}
    <div wire:ignore.self class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="accountName" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="accountName" wire:model="accountName">
                        @error('accountName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="accountType" class="form-label">Account Type</label>
                        <select class="form-select" id="accountType" wire:model="accountType">
                            <option value="">Select Type</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Revenue">Revenue</option>
                            <option value="Expense">Expense</option>
                        </select>
                        @error('accountType') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetInputs">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="addAccount()" wire:loading.attr="disabled">Add Account</button>
                </div>
            </div>
        </div>
    </div>


     <!-- Delete Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Confirm Delete</h5>
                    <button type="button" wire:click="resetInputs" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                 @if ($selectedAccountId)
                    @php
                        $account = collect($accounts)->where('id', $selectedAccountId)->first();
                    @endphp
                <div class="modal-body text-center py-4 fs-5">
                    <strong class="text-danger">Are you sure?</strong>
                    <p class="text-muted">You are about to delete: {{ $account->name }}</p>
                </div>
                @else
                <div class="modal-body text-center py-4 fs-5">
                    <strong class="text-danger">No account selected.</strong>
                </div>
                @endif
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" wire:click="resetInputs" class="btn btn-secondary px-4" data-bs-dismiss="modal"
                        >Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="deleteAccount()"
                        wire:loading.attr="disabled" data-bs-dismiss="modal">Yes, Delete</button>
                </div>
            </div>

        </div>
    </div>

    {{-- Alerts --}}
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
