<div>




    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-card-checklist fs-1 text-black"></i>
        <h2 class="mb-0 ms-3">Subscriptions</h2>
    </div>

    <!-- Subscriptions Table -->
    <div class="card shadow-sm rounded-3 p-4">
        <h5 class="card-title mb-4">Subscriptions</h5>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->id }}</td>
                            <td>{{ $subscription->customer_name }}</td>
                            <td>{{ $subscription->service_name }}</td>
                            <td>{{ $subscription->start_date }}</td>
                            <td>{{ $subscription->end_date ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($subscription->end_date === null || $subscription->end_date > now())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary me-2" wire:click="edit({{ $subscription->id }})">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $subscription->id }})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No subscriptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>




</div>
