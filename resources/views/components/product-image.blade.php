@props(['src' => null, 'alt' => ''])

<img
    {{ $attributes->merge(['class' => 'object-cover']) }}
    src="{{ product_image_url($src) }}"
    alt="{{ $alt }}"
    loading="lazy"
>
