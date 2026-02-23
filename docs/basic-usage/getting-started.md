---
title: Getting started
weight: 2
---

## The Blade component

Place the `<x-og-image>` component anywhere in your Blade view:

```blade
<x-og-image>
    <div class="w-full h-full bg-blue-900 text-white flex items-center justify-center">
        <h1 class="text-6xl font-bold">{{ $post->title }}</h1>
    </div>
</x-og-image>
```

The component outputs a hidden `<template>` tag (natively invisible in browsers) in the page body. The package middleware automatically injects the `og:image`, `twitter:image`, and `twitter:card` meta tags into the `<head>`:

```html
<head>
    <!-- your existing head content -->
    <meta property="og:image" content="https://yourapp.com/og-image/a1b2c3d4e5f6.jpeg">
    <meta name="twitter:image" content="https://yourapp.com/og-image/a1b2c3d4e5f6.jpeg">
    <meta name="twitter:card" content="summary_large_image">
</head>
<body>
    <template data-og-image>
        <div class="w-full h-full bg-blue-900 text-white flex items-center justify-center">
            <h1 class="text-6xl font-bold">My Post Title</h1>
        </div>
    </template>
</body>
```

The image URL contains a hash of your HTML content. When the content changes, the hash changes, so crawlers pick up the new image automatically.

The meta tags always point to `/og-image/{hash}.jpeg`. When that URL is first requested, the package generates the screenshot and serves it directly. The response includes `Cache-Control` headers, so CDNs like Cloudflare cache the image automatically.

## Migrating from manual meta tags

If your views already have `og:image`, `twitter:image`, or `twitter:card` meta tags, remove them. The package handles these automatically. Keep any other OG meta tags you have (`og:title`, `og:description`, `og:type`, `article:published_time`, etc.) — the package only manages the image-related tags.

## Using a Blade view

Instead of writing your OG image HTML inline, you can reference a Blade view:

```blade
<x-og-image view="og-image.post" :data="['title' => $post->title, 'author' => $post->author->name]" />
```

The view receives the `data` array as its variables:

```blade
{{-- resources/views/og-image/post.blade.php --}}
<div class="w-full h-full bg-blue-900 text-white flex items-center justify-center p-16">
    <div>
        <h1 class="text-6xl font-bold">{{ $title }}</h1>
        <p class="text-2xl mt-4">by {{ $author }}</p>
    </div>
</div>
```

This is useful when you reuse the same OG image layout across multiple pages or when the template is complex enough that you want it in its own file.

## Specifying the image format

By default, images are generated as JPEG. You can specify a different format:

```blade
<x-og-image format="webp">
    <div class="w-full h-full bg-gradient-to-r from-purple-500 to-pink-500 text-white flex items-center justify-center">
        <h1 class="text-6xl font-bold">{{ $title }}</h1>
    </div>
</x-og-image>
```

## Previewing your OG image

Append `?ogimage` to any page URL to see exactly what will be screenshotted. This renders just the template content at the configured dimensions (1200×630 by default), using the page's full `<head>` with all CSS and fonts.

## Design tips

- Design for 1200×630 pixels (the default size)
- Use `w-full h-full` on your root element to fill the entire viewport
- Use `flex` or `grid` for layout
- Keep text large, since it will be viewed as a thumbnail
- Preview your designs by visiting the page URL with `?ogimage` appended
