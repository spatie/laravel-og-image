<?php

return [

    /*
     * The filesystem disk used to store generated OG images.
     */
    'disk' => 'public',

    /*
     * The path within the disk where OG images will be stored.
     */
    'path' => 'og-images',

    /*
     * The dimensions of the generated OG images in pixels.
     */
    'width' => 1200,
    'height' => 630,

    /*
     * The default image format. Supported: "png", "jpeg", "webp".
     */
    'format' => 'png',

    /*
     * HTML tags injected into the <head> of the screenshot document.
     * By default, includes the Tailwind CSS CDN so you can use
     * Tailwind utility classes in your OG image templates.
     */
    'head' => [
        '<script src="https://cdn.tailwindcss.com"></script>',
    ],

    /*
     * The cache store used to temporarily store OG image HTML.
     * Set to null to use the default cache store.
     */
    'cache_store' => null,

    /*
     * How long to keep the HTML in cache. Set to null to cache forever.
     * The HTML is only needed until the image is generated, so a
     * few hours is usually sufficient.
     */
    'cache_ttl' => null,

    /*
     * The base URL used to generate OG image URLs.
     * Set to null to use the app URL.
     */
    'base_url' => null,

    /*
     * The route prefix for the OG image serving endpoint.
     */
    'route_prefix' => 'og-image',

    /*
     * Middleware applied to the OG image serving route.
     */
    'route_middleware' => ['web'],

    /*
     * Extra configuration passed to spatie/laravel-screenshot.
     * Supported keys: wait_until, device_scale_factor, wait_for_timeout, etc.
     */
    'screenshot' => [],
];
