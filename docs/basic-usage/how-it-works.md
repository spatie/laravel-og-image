---
title: How it works
weight: 1
---

When you share a link on social media, platforms like Twitter, Facebook, and LinkedIn display a preview image. These are called Open Graph (OG) images. This package lets you define that image as HTML in your Blade views, and automatically screenshots it to generate the actual image file.

## The big picture

There are three stages to understand:

1. Your page renders: the Blade component outputs a hidden HTML template, and the middleware injects meta tags into the `<head>`
2. A crawler fetches the image: the package generates a screenshot on the fly
3. Subsequent requests: the image is served directly from disk

Let's walk through each stage.

## Stage 1: Your page renders for the first time

When a visitor (or a crawler) loads your page, two things happen:

1. The `<x-og-image>` Blade component hashes the template HTML to produce a unique key (e.g. `a1b2c3d4e5f6`), stores the current page URL in cache keyed by that hash, and outputs a hidden `<template>` tag in the page body
2. The package middleware detects the `<template>` tag and injects `og:image`, `twitter:image`, and `twitter:card` meta tags into the `<head>`

The HTML in the response looks like this:

```html
<head>
    <!-- your existing head content -->
    <meta property="og:image" content="https://yourapp.com/og-image/a1b2c3d4e5f6.jpeg">
    <meta name="twitter:image" content="https://yourapp.com/og-image/a1b2c3d4e5f6.jpeg">
    <meta name="twitter:card" content="summary_large_image">
</head>
<body>
    <template data-og-image>
        <div class="...">My Post Title</div>
    </template>
</body>
```

The `<template>` tag is natively invisible in browsers, so visitors don't see it. The meta tags point to a route in your app (`/og-image/a1b2c3d4e5f6.jpeg`). The image doesn't exist yet, but that's fine. Crawlers will request it next.

## Stage 2: A crawler requests the image

When Twitter, Facebook, or LinkedIn sees the `og:image` meta tag, it makes a request to `https://yourapp.com/og-image/a1b2c3d4e5f6.jpeg`. The `a1b2c3d4e5f6` part is a hash generated from the HTML content of your OG image template. When you change the template content, the hash changes, producing a new URL. Here's what happens:

1. The request hits `OgImageController`, which checks if the image already exists on disk
2. If the image doesn't exist yet, the controller uses the hash from the URL to look up the original page URL from cache (stored there by the Blade component during rendering)
3. The controller tells headless Chrome to visit that page URL with `?ogimage` appended. This is a separate internal HTTP request to your app
4. `RenderOgImageMiddleware` detects the `?ogimage` parameter and replaces the response with a minimal HTML page: just your page's `<head>` (preserving all CSS, fonts, and Vite assets) and the template content, displayed at 1200x630 pixels
5. Chrome takes a screenshot of that page and saves it to the configured disk (default: `public`)
6. The controller serves the image back to the crawler. For local disks, the image is returned directly with `Cache-Control` headers. For remote disks (S3, etc.), a 301 redirect is issued to the storage URL

Because the screenshot uses your actual page's `<head>`, your OG image inherits all of your CSS, fonts, and Vite assets. No separate stylesheet configuration needed.

## Stage 3: Subsequent requests

Once the image exists on disk, subsequent requests to `/og-image/a1b2c3d4e5f6.jpeg` serve the image directly without taking another screenshot. The meta tags always use the same stable `/og-image/{hash}.{format}` URL, which makes this work well with page caching and CDNs like Cloudflare.

## Performance

The `/og-image/` route is designed to be fast and CDN-friendly:

- The route runs without any middleware (no sessions, CSRF, or cookies)
- Images are served directly with `Cache-Control` headers, so CDNs cache the response
- The meta tag URL is stable and content-hashed, so it works correctly with page caching (Cloudflare, Varnish, etc.)
- The component skips redundant cache writes when the hash is already known
