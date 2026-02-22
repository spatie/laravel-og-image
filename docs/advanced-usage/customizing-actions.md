---
title: Customizing actions
weight: 6
---

The package uses action classes for its core operations. You can replace any of them with your own implementation to customize the behavior. Each action has small, focused protected methods that you can override individually.

## Available actions

The following actions are registered in the `og-image` config file:

```php
'actions' => [
    'generate_og_image' => \Spatie\OgImage\Actions\GenerateOgImageAction::class,
    'inject_og_image_fallback' => \Spatie\OgImage\Actions\InjectOgImageFallbackAction::class,
    'render_og_image_screenshot' => \Spatie\OgImage\Actions\RenderOgImageScreenshotAction::class,
],
```

### GenerateOgImageAction

Handles the full flow when a social platform requests an OG image URL (`/og-image/{hash}.jpeg`): checking if the image exists on disk, looking up the page URL, taking a screenshot with locking, and serving the image directly.

Overridable methods: `serveImage`, `generateImage`.

### InjectOgImageFallbackAction

Handles injecting fallback OG image meta tags and template content into pages that don't have an `<x-og-image>` component.

Overridable methods: `renderFallback`, `hashContent`, `resolveScreenshotUrl`, `cachePageUrl`, `injectMetaTags`, `injectTemplate`, `injectBeforeClosingHead`, `injectBeforeClosingBody`.

### RenderOgImageScreenshotAction

Handles rendering the screenshot page when `?ogimage` is appended to a URL. Extracts the template content and head from the page, and renders the screenshot view.

Overridable methods: `extractTemplateContent`, `extractHead`, `renderScreenshot`.

## Overriding an action

Create a class that extends the default action and override the methods you want to customize:

```php
namespace App\Actions;

use Spatie\OgImage\Actions\GenerateOgImageAction;

class CustomGenerateOgImageAction extends GenerateOgImageAction
{
    protected function takeScreenshot(string $pageUrl, string $path, string $format): void
    {
        // Custom screenshot logic...
    }
}
```

Then register it in `config/og-image.php`:

```php
'actions' => [
    'generate_og_image' => \App\Actions\CustomGenerateOgImageAction::class,
],
```
