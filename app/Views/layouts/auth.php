<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('siapasn_favicon.ico') ?>">

    <?php $seo_noindex = true; ?>
    <?= $this->include('partials/_seo_head') ?>

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sa-primary:    #1a3a5c;
            --sa-accent:     #f5a623;
        }

        body {
            background: linear-gradient(135deg, #0f2744 0%, #1a3a5c 50%, #1e4976 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }

        .auth-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }

        .auth-header {
            text-align: center;
            background-color: #fff;
            padding: 1.5rem 1rem 0;
            border-bottom: 3px solid var(--sa-accent);
        }

        .auth-header img {
            max-height: 100px;
            max-width: 220px;
            object-fit: contain;
        }

        .auth-header h4 {
            margin: 0.5rem 0 0.75rem;
            font-weight: 700;
            font-size: 1rem;
            color: var(--sa-primary);
            letter-spacing: 0.3px;
        }

        .card-body {
            background-color: #fff;
        }

        .btn-primary {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
        }
        .btn-primary:hover {
            background-color: #0f2744;
            border-color: #0f2744;
        }

        a { color: var(--sa-primary); }
        a:hover { color: var(--sa-accent); }

        .form-control:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 58, 92, 0.2);
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="auth-header">
            <img src="<?= base_url('assets/images/SiapASN.png') ?>"
                 alt="<?= config('App')->appName ?? 'SiapASN Simulation Center' ?>">
            <h4><?= config('App')->appName ?? 'SiapASN Simulation Center' ?></h4>
        </div>
        <div class="card-body p-4">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
