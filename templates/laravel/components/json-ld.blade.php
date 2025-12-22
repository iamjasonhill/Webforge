@props([
    'type' => 'WebSite',
    'data' => [],
])

@php
    $baseSchema = [
        '@context' => 'https://schema.org',
        '@type' => $type,
    ];
    
    $schema = match($type) {
        'WebSite' => array_merge($baseSchema, [
            'name' => config('app.name'),
            'url' => config('app.url'),
        ]),
        'Organization' => array_merge($baseSchema, [
            'name' => config('app.name'),
            'url' => config('app.url'),
            'logo' => config('seo.logo', ''),
        ]),
        'LocalBusiness' => array_merge($baseSchema, [
            'name' => $data['name'] ?? config('app.name'),
            'url' => config('app.url'),
            'address' => $data['address'] ?? [],
            'telephone' => $data['telephone'] ?? '',
            'openingHours' => $data['openingHours'] ?? '',
        ]),
        'Article' => array_merge($baseSchema, [
            'headline' => $data['headline'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? '',
            ],
            'datePublished' => $data['datePublished'] ?? '',
            'dateModified' => $data['dateModified'] ?? $data['datePublished'] ?? '',
            'image' => $data['image'] ?? '',
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => config('seo.logo', ''),
            ],
        ]),
        'Product' => array_merge($baseSchema, [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? '',
            'brand' => [
                '@type' => 'Brand',
                'name' => $data['brand'] ?? config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $data['price'] ?? '',
                'priceCurrency' => $data['currency'] ?? 'AUD',
                'availability' => $data['availability'] ?? 'https://schema.org/InStock',
            ],
        ]),
        'FAQPage' => array_merge($baseSchema, [
            'mainEntity' => collect($data['questions'] ?? [])->map(fn($q) => [
                '@type' => 'Question',
                'name' => $q['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $q['answer'],
                ],
            ])->toArray(),
        ]),
        'BreadcrumbList' => array_merge($baseSchema, [
            'itemListElement' => $data['items'] ?? [],
        ]),
        default => array_merge($baseSchema, $data),
    };
    
    // Merge any additional data passed
    $schema = array_merge($schema, array_diff_key($data, array_flip(['name', 'headline', 'author', 'questions', 'items'])));
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

