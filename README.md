<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-og-image">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-og-image/html/dark.webp?123">
        <img alt="Logo for Laravel Open Graph Image" src="https://spatie.be/packages/header/laravel-og-image/html/light.webp?123">
      </picture>
    </a>

<h1>Generate OG images for your Laravel app</h1>
    
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-og-image.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-og-image)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-og-image/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-og-image/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-og-image/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/spatie/laravel-og-image/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-og-image.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-og-image)
    
</div>

This package makes it easy to generate Open Graph images for your Laravel application. Define your OG image HTML inline in your Blade views, and the package automatically generates screenshot images using [spatie/laravel-screenshot](https://github.com/spatie/laravel-screenshot), serves them via a dedicated route, and caches them on disk.

Your OG image templates inherit your page's existing CSS, fonts, and Vite assets. No separate CSS configuration needed.

No external API needed. Everything runs on your own server.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-og-image.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-og-image)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You'll find full documentation on [our documentation site](https://spatie.be/docs/laravel-og-image).

## Basic usage

Use the Blade component to define your OG image inline:

```blade
<x-og-image>
    <div class="w-full h-full bg-blue-900 text-white flex items-center justify-center">
        <h1 class="text-6xl font-bold">{{ $post->title }}</h1>
    </div>
</x-og-image>
```

This outputs a hidden `<template>` tag and `<meta>` tags pointing to a generated screenshot of your HTML at 1200×630 pixels.

## How it works

1. Your HTML is rendered inside a `<template data-og-image>` tag on the page
2. The page URL is cached, keyed by the md5 hash of the HTML content
3. Meta tags point to `/og-image/{hash}.jpeg`
4. When that URL is first requested, the page is visited with `?ogimage` appended, rendering just the template content with the page's full CSS at 1200×630
5. The generated image is saved to your public disk
6. Subsequent requests serve the image directly from disk

Preview any OG image by appending `?ogimage` to the page URL.

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-og-image
```

This package requires [spatie/laravel-screenshot](https://github.com/spatie/laravel-screenshot), which uses Browsershot under the hood. Make sure you have Node.js and a Chrome/Chromium binary installed.

You can optionally publish the config file:

```bash
php artisan vendor:publish --tag="og-image-config"
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
