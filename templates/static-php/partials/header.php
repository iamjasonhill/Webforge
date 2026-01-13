<?php
/**
 * Site Header Partial
 *
 * Variables (optional):
 * $skip_link_target - ID of main content (default: '#main-content')
 */
$config = require __DIR__.'/../config.php';
$skip_target = $skip_link_target ?? '#main-content';
?>

<!-- Skip Link for Accessibility -->
<a href="<?= $skip_target ?>" class="skip-link">Skip to main content</a>

<header class="site-header">
    <div class="container">
        <a href="/" class="site-logo">
            <?= htmlspecialchars($config['site_name']) ?>
        </a>
        <nav class="site-nav" aria-label="Main navigation">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about.php">About</a></li>
                <li><a href="/contact.php">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<style>
    .skip-link {
        position: absolute;
        top: -40px;
        left: 0;
        background: #4f46e5;
        color: white;
        padding: 8px 16px;
        z-index: 100;
        text-decoration: none;
    }

    .skip-link:focus {
        top: 0;
    }
</style>