<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceApp Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: #adb5bd;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            font-weight: 500;
            border-radius: 6px;
            padding: 10px 15px;
            transition: 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }

        .sidebar .nav-link.active {
            background-color: #495057;
            color: #fff;
        }

        /* Top Navbar */
        .top-navbar {
            height: 60px;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .content-wrapper {
            padding-top: 20px;
            padding-bottom: 20px;
        }
    </style>

    @livewireStyles
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar p-3">
                <h4 class="text-center mb-4 fw-bold text-white">ServiceApp</h4>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ Request::is('customers') ? 'active' : '' }}" href="/customers"><i class="bi bi-people"></i> Customers</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ Request::is('services') ? 'active' : '' }}" href="/services"><i class="bi bi-gear"></i> Services</a>
                    </li>
                                <li class="nav-item mb-2">
                                    <a class="nav-link {{ Request::is('subscription') ? 'active' : '' }}" href="/subscription"><i class="bi bi-card-checklist"></i> Subscription</a>
                                </li>
                                <li class="nav-item mb-2">
                                    <a class="nav-link {{ Request::is('currencies') ? 'active' : '' }}" href="/currencies"><i class="bi bi-currency-exchange"></i> Currencies</a>
                                </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-4">



                <!-- Content -->
                <div class="content-wrapper">
                    @yield('content')
                </div>

            </main>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>

</html>
