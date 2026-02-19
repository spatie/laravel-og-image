---
title: Installation & setup
weight: 3
---

You can install the package via composer:

```bash
composer require spatie/laravel-og-image
```

## Configuring the screenshot driver

This package uses [spatie/laravel-screenshot](https://github.com/spatie/laravel-screenshot) to take screenshots of your OG image HTML. You can use either Browsershot or Cloudflare to take these screenshots.

### Browsershot (default)

Browsershot is the default driver and requires Node.js and Chrome/Chromium on your server. No extra configuration is needed if these are already installed.

See the [Browsershot requirements](https://spatie.be/docs/browsershot/v4/requirements) and [installation instructions](https://spatie.be/docs/browsershot/v4/installation-setup) for how to set these up, including instructions for [Forge](https://spatie.be/docs/browsershot/v4/installation-setup#content-forge).

### Cloudflare

If you don't want to install Node.js and Chrome on your server, you can use [Cloudflare's Browser Rendering API](https://developers.cloudflare.com/browser-rendering/) instead.

Add this to your `AppServiceProvider`:

```php
use Spatie\OgImage\Facades\OgImage;

public function boot(): void
{
    OgImage::useCloudflare(
        apiToken: env('CLOUDFLARE_API_TOKEN'),
        accountId: env('CLOUDFLARE_ACCOUNT_ID'),
    );
}
```

Then add your credentials to `.env`:

```
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id
```

You can find your account ID in the Cloudflare dashboard URL (`https://dash.cloudflare.com/<account-id>`). To create an API token, go to [API Tokens](https://dash.cloudflare.com/profile/api-tokens) and create a token with the `Workers Scripts: Edit` permission.

## Publishing the config file

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --tag=og-image-config
```

This is the content of the published config file:

```php
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
     * The default image format. Supported: "jpeg", "png", "webp".
     */
    'format' => 'jpeg',

    /*
     * The actions used by this package. You can replace any of them with
     * your own class to customize the behavior. Your custom class should
     * extend the default action.
     *
     * Learn more: https://spatie.be/docs/laravel-og-image/v1/advanced-usage/customizing-actions
     */
    'actions' => [
        'generate_og_image' => \Spatie\OgImage\Actions\GenerateOgImageAction::class,
        'inject_og_image_fallback' => \Spatie\OgImage\Actions\InjectOgImageFallbackAction::class,
        'render_og_image_screenshot' => \Spatie\OgImage\Actions\RenderOgImageScreenshotAction::class,
    ],

];
```
