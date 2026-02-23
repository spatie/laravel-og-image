---
title: Defining fallback images
weight: 4
---

When a page doesn't use the `<x-og-image>` component, no OG image meta tags are generated. You can register a fallback to automatically provide an OG image for these pages.

## Registering a fallback

In your `AppServiceProvider`, register a closure that receives the current request and returns a view:

```php
use Illuminate\Http\Request;
use Spatie\OgImage\Facades\OgImage;

public function boot(): void
{
    OgImage::fallbackUsing(function (Request $request) {
        return view('og-image.fallback', [
            'title' => config('app.name'),
        ]);
    });
}
```

The closure receives the full `Request` object, so you can use route parameters, model bindings, or any other request data to customize the fallback:

```php
OgImage::fallbackUsing(function (Request $request) {
    $title = $request->route('post')?->title ?? config('app.name');

    return view('og-image.fallback', [
        'title' => $title,
        'url' => $request->url(),
    ]);
});
```

The fallback view should be a regular Blade view with just the HTML content, no layout or scripts needed. Like `<x-og-image>`, the screenshot inherits the page's `<head>`, so your CSS, fonts, and Vite assets are available automatically.

```blade
{{-- resources/views/og-image/fallback.blade.php --}}
<div class="w-full h-full bg-blue-900 text-white flex items-center justify-center p-16">
    <h1 class="text-6xl font-bold">{{ $title }}</h1>
</div>
```

## Skipping the fallback for specific pages

Return `null` from the closure to skip the fallback for a specific request:

```php
OgImage::fallbackUsing(function (Request $request) {
    if ($request->routeIs('admin.*')) {
        return null;
    }

    return view('og-image.fallback', [
        'title' => config('app.name'),
    ]);
});
```

## How it works

When a page is rendered without an `<x-og-image>` component and a fallback is registered, the middleware will:

1. Call your closure with the current request
2. Render the returned view
3. Inject the `<template data-og-image>` tag and `og:image` meta tags into the response

From that point on, the fallback image goes through the same screenshot and caching pipeline as any explicit `<x-og-image>`. Pages that do have an `<x-og-image>` component are never affected by the fallback.
