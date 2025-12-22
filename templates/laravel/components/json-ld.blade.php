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
        'BreadcrumbList' => array_merge($baseSchema, [
            'itemListElement' => $data['items'] ?? [],
        ]),
        default => array_merge($baseSchema, $data),
    };
    
    $schema = array_merge($schema, $data);
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
