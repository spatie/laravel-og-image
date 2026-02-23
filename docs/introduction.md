---
title: Introduction
weight: 1
---

This package makes it easy to generate Open Graph images for your Laravel application. Define your OG image HTML inline in your Blade views, and the package automatically generates screenshot images, serves them via a dedicated route, and caches them on disk.

Here's a quick example using the Blade component:

```blade
<x-og-image>
    <div class="w-full h-full bg-blue-900 text-white flex items-center justify-center">
        <h1 class="text-6xl font-bold">{{ $post->title }}</h1>
    </div>
</x-og-image>
```

This will render a hidden `<template>` tag containing your HTML. The package middleware automatically injects the `og:image` and `twitter:image` meta tags into your page's `<head>`. The screenshot is taken at 1200×630 pixels (at 2x resolution for retina sharpness), the standard OG image size.

Because the OG image template lives on the actual page, it inherits your page's existing CSS, fonts, and Vite assets. No separate CSS configuration needed.

The approach of using a `<template>` tag to define OG images inline with your page's own CSS is inspired by [OGKit](https://ogkit.dev) by [Peter Suhm](https://x.com/petersuhm). If you don't want to self-host the generation of OG images, OGKit is a great alternative.


## We got badges

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-og-image.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-og-image)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-og-image/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-og-image/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-og-image.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-og-image)
