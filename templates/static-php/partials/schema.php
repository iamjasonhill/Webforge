<?php

/**
 * JSON-LD Schema Helper
 *
 * Usage:
 * <?php echo schema('WebSite'); ?>
 * <?php echo schema('Organization', ['logo' => '/logo.png']); ?>
 * <?php echo schema('Article', ['headline' => 'My Title', 'author' => 'John']); ?>
 */
function schema(string $type, array $data = []): string
{
    $config = require __DIR__.'/../config.php';

    $baseSchema = [
        '@context' => 'https://schema.org',
        '@type' => $type,
    ];

    switch ($type) {
        case 'WebSite':
            $schema = array_merge($baseSchema, [
                'name' => $config['site_name'],
                'url' => $config['site_url'],
            ], $data);
            break;

        case 'Organization':
            $schema = array_merge($baseSchema, [
                'name' => $config['site_name'],
                'url' => $config['site_url'],
                'logo' => $data['logo'] ?? '',
            ], $data);
            break;

        case 'LocalBusiness':
            $schema = array_merge($baseSchema, [
                'name' => $data['name'] ?? $config['site_name'],
                'url' => $config['site_url'],
                'address' => $data['address'] ?? [],
                'telephone' => $data['telephone'] ?? '',
            ], $data);
            break;

        case 'Article':
            $schema = array_merge($baseSchema, [
                'headline' => $data['headline'] ?? '',
                'author' => [
                    '@type' => 'Person',
                    'name' => $data['author'] ?? '',
                ],
                'datePublished' => $data['datePublished'] ?? '',
                'dateModified' => $data['dateModified'] ?? $data['datePublished'] ?? '',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $config['site_name'],
                ],
            ], $data);
            break;

        case 'FAQPage':
            $questions = array_map(fn ($q) => [
                '@type' => 'Question',
                'name' => $q['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $q['answer'],
                ],
            ], $data['questions'] ?? []);

            $schema = array_merge($baseSchema, [
                'mainEntity' => $questions,
            ]);
            break;

        case 'BreadcrumbList':
            $items = array_map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ], $data['items'] ?? [], array_keys($data['items'] ?? []));

            $schema = array_merge($baseSchema, [
                'itemListElement' => $items,
            ]);
            break;

        default:
            $schema = array_merge($baseSchema, $data);
    }

    return '<script type="application/ld+json">'.json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).'</script>';
}
