<?php
/**
 * Site Footer Partial
 */
$config = require __DIR__.'/../config.php';
$year = date('Y');
?>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= $year ?> <?= htmlspecialchars($config['site_name']) ?>. All rights reserved.</p>
    </div>
</footer>