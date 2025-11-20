<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f0f4ff, #d9e7ff);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error-container {
            text-align: center;
            max-width: 700px;
            padding: 30px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #0d6efd;
        }

        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 30px;
        }

        .btn-home {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
        }

        .search-bar {
            max-width: 400px;
            margin: 20px auto;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .illustration {
            max-width: 300px;
            margin: 20px auto;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <img src="https://cdn.dribbble.com/users/285475/screenshots/2083086/dribbble_1.gif" alt="404 Illustration" class="illustration img-fluid">

        <!-- Error Code -->
        <div class="error-code text-danger">404</div>
        <!-- Message -->
        <div class="error-message">Sorry, we couldn't find the page you were looking for.</div>

        <!-- Back Home Button -->
        <a href="/" class="btn btn-outline-primary btn-home mt-3"><i class="bi bi-house-door-fill"></i> Go Back Home</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
