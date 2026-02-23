---
title: Customizing the page URL
weight: 3
---

When the package caches which URL to screenshot for an OG image, it uses the request URL without query parameters by default. This means `/posts?page=2` and `/posts?page=3` would both resolve to `/posts` for the screenshot.

For most pages this is the right behavior, since query parameters like pagination don't affect the OG image. But if your OG image content varies based on query parameters, you can customize how the URL is resolved.

## Changing the URL resolution

In your `AppServiceProvider`, register a closure that receives the current request and returns the URL to use:

```php
use Illuminate\Http\Request;
use Spatie\OgImage\Facades\OgImage;

public function boot(): void
{
    OgImage::resolveScreenshotUrlUsing(function (Request $request) {
        return $request->fullUrl();
    });
}
```

This will include query parameters in the cached URL, so each unique combination of path and query parameters gets its own screenshot.

You can also selectively include only certain query parameters:

```php
OgImage::resolveScreenshotUrlUsing(function (Request $request) {
    $url = $request->url();

    if ($request->has('category')) {
        $url .= '?category=' . $request->query('category');
    }

    return $url;
});
```
