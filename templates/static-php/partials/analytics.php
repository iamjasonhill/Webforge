<?php
/**
 * Analytics Partial
 * Include before </head>
 *
 * Variables (optional):
 * $analytics_id - Override config GA ID
 * $analytics_provider - 'gtag' (default), 'gtm', or 'plausible'
 */
$config = require __DIR__.'/../config.php';

$id = $analytics_id ?? $config['google_analytics_id'];
$provider = $analytics_provider ?? 'gtag';

if (empty($id)) {
    return;
}
?>

<?php if ($provider === 'gtag') { ?>
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($id) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($id) ?>');
    </script>
<?php } ?>

<?php if ($provider === 'gtm') { ?>
    <!-- Google Tag Manager -->
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '<?= htmlspecialchars($id) ?>');
    </script>
<?php } ?>

<?php if ($provider === 'plausible') { ?>
    <!-- Plausible Analytics -->
    <script defer data-domain="<?= parse_url($config['site_url'], PHP_URL_HOST) ?>"
        src="https://plausible.io/js/script.js"></script>
<?php } ?>