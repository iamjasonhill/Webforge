<?php
/**
 * 404 Error Page
 */

$page_title = 'Page Not Found';
$page_description = 'The page you are looking for does not exist.';
$noindex = true;

$config = require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/partials/head.php'; ?>
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
        }

        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #d1d5db;
            margin: 0;
        }

        .error-title {
            font-size: 1.5rem;
            color: #1f2937;
            margin-top: 1rem;
        }

        .error-message {
            color: #6b7280;
            margin-top: 0.5rem;
        }

        .error-actions {
            margin-top: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            margin: 0 0.5rem;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="error-page">
        <div>
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Page Not Found</h2>
            <p class="error-message">Sorry, the page you're looking for doesn't exist.</p>
            <div class="error-actions">
                <a href="/" class="btn">Go Home</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            </div>
        </div>
    </div>
</body>

</html>