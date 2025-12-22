@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'priority' => false,
    'class' => '',
])
<img 
 src="{{ $src }}"
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    @if($lazy && !$priority) loading="lazy" @endif
    @if($priority) fetchpriority="high" @endif
    decoding="async"
    @class([$class])
    {{ $attributes }}
>
