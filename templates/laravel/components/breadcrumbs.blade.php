@props([
    'items' => [],
])
@php
    $schemaItems = [];
    foreach ($items as $index => $item) {
        $schemaItems[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['url'] ?? null,
        ];
    }
@endphp

{{-- Visual Breadcrumbs --}}
<nav aria-label="Breadcrumb" class="text-sm text-gray-600 dark:text-gray-400">
<ol class="flex items-center space-x-2">
    @foreach($items as $index => $item)
        <li class="flex items-center">
               @if($index > 0)
                         <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif

               @if(isset($item['url']) && $index < count($items) - 1)
                <a href="{{ $item['url'] }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        {{ $item['name'] }}
                </a>
               @else
                    <span class="text-gray-900 dark:text-gray-200 font-medium">
                            {{ $item['name'] }}
                        </span>
                @endif
                </li>
    @endforeach
    </ol>
</nav>

{{-- Schema.org Markup --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $schemaItems,
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
