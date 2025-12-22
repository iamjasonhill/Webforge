<?php
/**
 * Example Index Page
 * Demonstrates how to use the partials
 */

// Page-specific SEO
$page_title = 'Home';
$page_description = 'Welcome to our website';

// Include schema helper
require_once __DIR__ . '/partials/schema.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/partials/head.php'; ?>
    <?php include __DIR__ . '/partials/analytics.php'; ?>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main id="main-content">
        <section class="hero">
            <h1>Welcome to <?= htmlspecialchars($config['site_name']) ?></h1>
            <p>Your content goes here.</p>
        </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <!-- JSON-LD Schema -->
    <?= schema('WebSite') ?>
    <?= schema('Organization') ?>
</body>

</html>