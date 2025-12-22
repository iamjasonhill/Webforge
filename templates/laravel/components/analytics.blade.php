@props([
    'id' => null,
    'provider' => 'gtag', // gtag, gtm, plausible
])

@php
    $measurementId = $id ?? config('services.google.analytics_id');
@endphp

@if($measurementId)
    @switch($provider)
        @case('gtag')
            {{-- Google Analytics 4 (gtag.js) --}}
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $measurementId }}');
            </script>
            @break
            
        @case('gtm')
            {{-- Google Tag Manager --}}
            <script>
                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','{{ $measurementId }}');
            </script>
            @break
            
        @case('plausible')
            {{-- Plausible Analytics (privacy-friendly) --}}
            <script defer data-domain="{{ parse_url(config('app.url'), PHP_URL_HOST) }}" src="https://plausible.io/js/script.js"></script>
            @break
    @endswitch
@endif
