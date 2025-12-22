@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'canonical' => null,
])

@php
    $siteName = config('app.name');
    $pageTitle = $title ? "{$title} | {$siteName}" : $siteName;
    $pageDescription = $description ?? config('seo.default_description', '');
    $pageImage = $image ?? config('seo.default_image', '');
    $pageUrl = $canonical ?? request()->url();
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ $pageUrl }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
@if($pageImage)
<meta property="og:image" content="{{ $pageImage }}">
@endif
<meta property="og:site_name" content="{{ $siteName }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $pageUrl }}">
<meta property="twitter:title" content="{{ $pageTitle }}">
<meta property="twitter:description" content="{{ $pageDescription }}">
@if($pageImage)
<meta property="twitter:image" content="{{ $pageImage }}">
@endif

{{ $slot ?? '' }}
