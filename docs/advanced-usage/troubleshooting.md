---
title: Troubleshooting
weight: 7
---

## Chrome/Chromium not found

When using the default Browsershot driver, a Chrome or Chromium binary must be installed on your server. If you get an error about Chrome not being found, make sure it's installed.

On Ubuntu/Debian:

```bash
apt-get install -y chromium-browser
```

On macOS:

```bash
brew install --cask chromium
```

If Chrome is installed at a non-standard path, you can configure it using the `configureScreenshot` method in a service provider:

```php
use Spatie\OgImage\Facades\OgImage;

OgImage::configureScreenshot(function ($screenshot) {
    $screenshot->setChromePath('/usr/bin/chromium');
});
```

## Blank or broken screenshots

If your screenshots appear blank or have missing styles/fonts, it's usually because CSS or web fonts haven't finished loading before the screenshot is taken.

Common fixes:

- Make sure your CSS is loaded via `<link>` tags in `<head>` (the package preserves the full `<head>` during screenshots)
- For web fonts, ensure they're loaded from `<head>`. The package carries over all `<link>` and `<style>` tags
- Add a delay before the screenshot is taken:

```php
OgImage::configureScreenshot(function ($screenshot) {
    $screenshot->waitUntilNetworkIdle();
});
```

## Timeout issues

By default, the package uses a 60-second lock timeout when generating images. If you're generating large images or your server is slow, you may need to increase this:

```php
// config/og-image.php
'lock_timeout' => 120,
```

## Cache invalidation

The package generates a hash based on the HTML content of your OG image template. When the content changes, the hash changes, and a new image is generated automatically.

To manually clear all generated images:

```bash
php artisan og-image:clear
```

Note that the URL-to-hash mapping lives in your application cache. If you clear the cache (`php artisan cache:clear`), the package will re-cache the mappings on the next page visit, and regenerate screenshots as needed.

## Debugging with `?ogimage`

Append `?ogimage` to any page URL to preview exactly what will be screenshotted. This renders just the template content at the configured dimensions, using the page's full `<head>`.

```
https://yourapp.com/blog/my-post?ogimage
```

This is useful for:

- Checking if your OG image HTML renders correctly
- Verifying that CSS and fonts are loading
- Testing custom dimensions set via `width` and `height` attributes

