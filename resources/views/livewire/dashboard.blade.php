<div>
    <div class="container mt-5">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-speedometer2 fs-1 text-black"></i>
            <h2 class="mb-0 ms-3">Dashboard</h2>
        </div>


        <div class="row g-4">
            <!--Total Customers Card -->
            <div class="col-md-6 mb-4">
                <a href="/customers" class="text-decoration-none">
                    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-people fs-1 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-dark">Total Customers</h5>
                                <h3 class="card-text text-dark">{{ $customers['totalCustomers'] }}</h3>
                                <small class="text-success">+{{ $customers['todayCustomers'] }} today</small>
                                <small class="text-muted">{{ number_format($customers['todayPercent'], 2) }}% of
                                    total</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!--Active Services Card -->
            <div class="col-md-6 mb-4">
                <a href="/services" class="text-decoration-none">
                    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-gear fs-1 text-success"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-dark">Active Services</h5>
                                <h3 class="card-text text-dark">{{ $services['activeServices'] }}</h3>
                                <small class="text-success">+{{ $services['todayServices'] }} today</small>
                                <small class="text-muted">{{ number_format($services['todayPercent'], 2) }}% of
                                    total</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <!--Total Subscriptions Card -->
            <div class="col-md-6 mb-4">
                <a href="/subscription" class="text-decoration-none">
                    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">

                                <div> <i class="bi bi-card-checklist fs-1 text-secondary"></i></div>
                            </div>
                            <div>
                                <h5 class="card-title text-dark">Total Subscriptions</h5>
                                <h3 class="card-text text-dark">{{ $subscriptions['totalSubscriptions'] }}</h3>
                                <small class="text-success">+{{ $subscriptions['todaySubscriptions'] }}
                                    today</small>
                                <small class="text-muted">{{ number_format($subscriptions['todayPercent'], 2) }}% of
                                    total</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!--Total paid invoices Card -->
            <div class="col-md-6 mb-4">
                <a href="/paid-invoices" class="text-decoration-none">
                    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3">

                                <div> <i class="bi bi-receipt fs-1 text-warning"></i></div>
                            </div>
                            <div>
                                <h5 class="card-title text-dark">Total Invoices</h5>
                                <h3 class="card-text text-dark">{{ $invoices['totalInvoices'] }}</h3>
                                <small class="text-success">+{{ $invoices['todayInvoices'] }}
                                    today</small>
                                <small class="text-muted">{{ number_format($invoices['todayPercent'], 2) }}% of
                                    total</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <!--Total Cash Assets Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <div> <i class="bi bi-wallet2 fs-1 text-info"></i></div>
                            <div>
                                <h5 class="card-title text-dark">Total Cash Assets</h5>
                                <h3 class="card-text text-dark">{{ number_format($totals['Cash_assets'], 2) }}</h3>
                                <small class="text-muted">Debits:
                                    {{ number_format($totals['Cash_debts'], 2) }}</small>
                                <small class="text-muted">Credits:
                                    {{ number_format($totals['Cash_credits'], 2) }}</small>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
