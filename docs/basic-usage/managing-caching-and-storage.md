---
title: Managing caching and storage
weight: 4
---

When a visitor loads a page with an `<x-og-image>` component, the package:

1. Hashes the template HTML to generate a unique key (e.g. `abc123`)
2. Stores the current page URL in the Laravel cache, keyed by that hash
3. Outputs meta tags pointing to the image URL: `/og-image/abc123.jpeg`

Later, when a social platform like Twitter or Facebook crawls that image URL, the package:

1. Looks up the page URL from cache using the hash
2. Visits that page to take a screenshot
3. Stores the resulting image on disk
4. Returns the image

On subsequent requests for the same image, it is served directly from disk without taking another screenshot.

The Laravel cache only needs to hold the page URL long enough for the image to be generated. After that, the image lives on disk permanently.

## Content-hash URLs

Because image URLs are based on the md5 hash of the HTML content, changing the template content automatically produces a new URL. Old images remain on disk until you clear them.

## Configuring the disk

By default, images are stored on the `public` disk at `og-images`. You can change this via the [customizing screenshots](/docs/laravel-og-image/v1/basic-usage/customizing-screenshots) page:

```php
use Spatie\OgImage\Facades\OgImage;

OgImage::disk('s3', 'og-images');
```
