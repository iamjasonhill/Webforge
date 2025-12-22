<?php
/**
 * SEO Head Partial
 * Include in <head> section
 * 
 * Variables (set before including):
 * $page_title - Page title
 * $page_description - Page description (optional, uses site default)
 * $page_image - OG image URL (optional, uses site default)
 * $canonical_url - Canonical URL (optional, uses current URL)
 * $noindex - Set to true to noindex page (optional)
 */

$config = require __DIR__ . '/../config.php';

$title = isset($page_title) ? $page_title . ' | ' . $config['site_name'] : $config['site_name'];
$description = $page_description ?? $config['site_description'];
$image = $page_image ?? $config['site_image'];
$canonical = $canonical_url ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!-- Primary Meta Tags -->
<title><?= htmlspecialchars($title) ?></title>
<meta name="title" content="<?= htmlspecialchars($title) ?>">
<meta name="description" content="<?= htmlspecialchars($description) ?>">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<?php if (isset($noindex) && $noindex): ?>
    <meta name="robots" content="noindex, nofollow">
<?php endif; ?>

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:title" content="<?= htmlspecialchars($title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($description) ?>">
<?php if ($image): ?>
    <meta property="og:image" content="<?= htmlspecialchars($image) ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= htmlspecialchars($config['site_name']) ?>">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="twitter:title" content="<?= htmlspecialchars($title) ?>">
<meta property="twitter:description" content="<?= htmlspecialchars($description) ?>">
<?php if ($image): ?>
    <meta property="twitter:image" content="<?= htmlspecialchars($image) ?>">
<?php endif; ?>