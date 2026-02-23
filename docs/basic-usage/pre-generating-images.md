---
title: Pre-generating images
weight: 6
---

By default, OG images are generated lazily, on the first request from a crawler. This means the first crawler to request the image triggers a Chrome screenshot, which takes a few seconds. If many new pages go live at the same time, this can lead to multiple concurrent screenshot processes and put load on your server.

To avoid this, you can pre-generate images ahead of time.

## Using the artisan command

```bash
php artisan og-image:generate https://yourapp.com/page1 https://yourapp.com/page2
```

## Using `generateForUrl()`

You can generate an OG image programmatically by passing a page URL:

```php
use Spatie\OgImage\Facades\OgImage;

$imageUrl = OgImage::generateForUrl('https://yourapp.com/blog/my-post');
```

This fetches the page, extracts the `<x-og-image>` template, takes a screenshot, and saves it to disk.

## Generating after publishing content

A common pattern is to dispatch a queued job after saving content, so the OG image is ready before any crawler requests it:

```php
use Spatie\OgImage\Facades\OgImage;

class PublishPostAction
{
    public function execute(Post $post): void
    {
        // ... publish logic ...

        dispatch(function () use ($post) {
            OgImage::generateForUrl($post->url);
        });
    }
}
```
