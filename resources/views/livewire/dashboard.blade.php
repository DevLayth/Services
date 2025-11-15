<div>
    <div class="container mt-5">
  <h1 class="mb-4">Dashboard</h1>

  <div class="row g-4">
    <!-- Card 1 -->
   <div class="col-md-6 mb-4">
  <a href="/customers" class="text-decoration-none">
    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
      <div class="card-body d-flex align-items-center">
        <div class="me-3">
          <i class="bi bi-briefcase-fill fs-1 text-warning"></i>
        </div>
        <div>
          <h5 class="card-title text-dark">Total Customers</h5>
          <h3 class="card-text text-dark">{{ $customers['totalCustomers'] }}</h3>
          <small class="text-success">+{{ $customers['todayCustomers'] }} today</small>
        </div>
      </div>
    </div>
  </a>
</div>

    <!-- Card 2 -->
<div class="col-md-6 mb-4">
  <a href="/services" class="text-decoration-none">
    <div class="card h-100 shadow-sm rounded-4 border-0 bg-white cursor-pointer hover-scale">
      <div class="card-body d-flex align-items-center">
        <div class="me-3">
          <i class="bi bi-briefcase-fill fs-1 text-warning"></i>
        </div>
        <div>
          <h5 class="card-title text-dark">Active Services</h5>
          <h3 class="card-text text-dark">{{ $services['activeServices'] }}</h3>
          <small class="text-success">+{{ $services['todayServices'] }} today</small>
        </div>
      </div>
    </div>
  </a>
</div>


</div>
