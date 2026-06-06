<?php

if (! function_exists('product_image_url')) {
    function product_image_url(?string $image): string
    {
        return $image ?: asset('images/placeholder-tire.svg');
    }
}

if (! function_exists('format_kes')) {
    function format_kes(float|string|null $amount): string
    {
        if ($amount === null) {
            return 'KES 0';
        }

        return 'KES '.number_format((float) $amount, 0);
    }
}
