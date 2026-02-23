---
title: Using a custom screenshot driver
weight: 4
---

By default, the package uses [Browsershot](https://github.com/spatie/browsershot) (headless Chrome) to take screenshots. You can also use Cloudflare's Browser Rendering by calling `useCloudflare()`. See [customizing screenshots](/docs/laravel-og-image/v1/basic-usage/customizing-screenshots) for details.

If neither of these fits your needs, you can create your own screenshot driver.

## Creating a driver

A custom driver must implement the `ScreenshotDriver` interface from [spatie/laravel-screenshot](https://github.com/spatie/laravel-screenshot):

```php
use Spatie\LaravelScreenshot\Drivers\ScreenshotDriver;
use Spatie\LaravelScreenshot\ScreenshotOptions;

class MyScreenshotDriver implements ScreenshotDriver
{
    public function generateScreenshot(
        string $input,
        bool $isHtml,
        ScreenshotOptions $options,
    ): string {
        // Take the screenshot and return the image as a binary string.
        //
        // $input is a URL (when $isHtml is false) or an HTML string (when $isHtml is true).
        // $options contains width, height, type, deviceScaleFactor, etc.
    }

    public function saveScreenshot(
        string $input,
        bool $isHtml,
        ScreenshotOptions $options,
        string $path,
    ): void {
        // Take the screenshot and save it to $path.
    }
}
```

The `ScreenshotOptions` object gives you access to all configured options like `$options->width`, `$options->height`, `$options->type` (an `ImageType` enum), `$options->deviceScaleFactor`, and more.

## Registering the driver

```php
use Spatie\OgImage\Facades\OgImage;

OgImage::useDriver(new MyScreenshotDriver());
```
