<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #dc3545;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="text-center">
            <div class="error-code">403</div>
            <h1 class="h3 mt-3 mb-2 text-dark">Akses Ditolak</h1>
            <p class="text-muted mb-4">
                Anda tidak memiliki izin untuk mengakses halaman ini.<br>
                Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
            </p>
            <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                &larr; Kembali
            </a>
            <a href="/" class="btn btn-primary">
                Beranda
            </a>
        </div>
    </div>
</body>
</html>
