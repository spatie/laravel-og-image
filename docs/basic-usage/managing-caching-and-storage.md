---
title: Caching and storage
weight: 5
---

## Content-hashed URLs

Image URLs are based on an md5 hash of the HTML content. When you change the template content, the hash changes and a new URL is produced. Crawlers pick up the new image automatically. Old images remain on disk until you [clear them](/docs/laravel-og-image/v1/basic-usage/clearing-generated-images).

## Configuring the disk

By default, images are stored on the `public` disk at `og-images/`. You can change both via the `OgImage` facade:

```php
use Spatie\OgImage\Facades\OgImage;

OgImage::disk('s3', 'og-images');
```

## Serving with Cloudflare (recommended)

If your site is behind Cloudflare, no extra configuration is needed. When the first crawler requests an OG image:

1. The request passes through Cloudflare to your server
2. PHP generates (or reads) the image and responds with `Cache-Control: public, max-age=86400`
3. Cloudflare caches the response

All subsequent requests for that image are served directly from Cloudflare's edge — PHP is never hit again (until the cache expires). Since image URLs are content-hashed, different content always produces a different URL, so stale cache is not a concern.

You can adjust the cache duration in your config:

```php
// config/og-image.php
'redirect_cache_max_age' => 60 * 60 * 24 * 7, // 7 days
```

## Serving without a CDN

Without a CDN like Cloudflare, every request to `/og-image/{hash}.jpeg` hits PHP. This works, but each request occupies a PHP-FPM worker to serve a static file.

You can add an nginx rule that serves already-generated images directly from disk, bypassing PHP entirely:

```nginx
location ~ ^/og-image/([a-f0-9]+\.(jpeg|jpg|png|webp))$ {
    try_files /storage/og-images/$1 /index.php?$query_string;
}
```

Place this before the `location /` block in your nginx site config.

This tells nginx to first check if the image exists at `/storage/og-images/{hash}.{format}`. If it does, nginx serves it directly. If not, the request falls through to PHP, which generates the image and saves it to disk. The next request for that image is served by nginx without PHP.

### Forge

If you're using Laravel Forge, you can add this rule via the Nginx tab on the site settings page. Add it to the "before" section or edit the site's nginx config directly.

## Serving with S3 or other remote disks

When using S3 as your storage disk, the package detects that it is a remote disk and automatically issues a 301 redirect to the S3 URL instead of streaming the image through PHP. This means:

1. The first request hits PHP, which issues a 301 redirect to `https://your-bucket.s3.amazonaws.com/og-images/{hash}.jpeg`
2. The client fetches the image directly from S3
3. CDNs cache the 301 redirect, so subsequent requests never hit PHP

This keeps PHP out of the image serving path entirely. If your S3 bucket is behind CloudFront, crawlers end up fetching the image from CloudFront's edge.

The nginx `try_files` optimization does not apply to S3, since the files are not on the local filesystem. The redirect approach is used automatically instead.

## Pre-generating images

If you want to generate OG images ahead of time (for example, after a deploy), you can use the artisan command:

```bash
php artisan og-image:generate https://yourapp.com/page1 https://yourapp.com/page2
```

Or programmatically:

```php
use Spatie\OgImage\Facades\OgImage;

$imageUrl = OgImage::generateForUrl('https://yourapp.com/blog/my-post');
```
