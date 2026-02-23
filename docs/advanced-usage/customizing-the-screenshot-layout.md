---
title: Customizing the screenshot layout
weight: 5
---

When you visit a page with `?ogimage`, the package renders a minimal HTML document that wraps your template content. This document includes the page's `<head>` (so CSS and fonts work) and a reset style that sets the viewport to the configured dimensions.

If you need to customize this layout (for example, to add extra styles, scripts, or change the document structure), you can publish the view:

```bash
php artisan vendor:publish --tag=og-image-views
```

This publishes `screenshot.blade.php` to `resources/views/vendor/og-image/`. The view receives these variables:

### $head

The contents of the original page's `<head>` tag, including all CSS, fonts, and Vite assets.

### $templateContent

The HTML from your `<x-og-image>` component.

### $width

The configured width in pixels (default: 1200).

### $height

The configured height in pixels (default: 630).

For even deeper control over how the screenshot page is rendered, you can override the `RenderOgImageScreenshotAction`. See [customizing actions](/docs/laravel-og-image/v1/advanced-usage/customizing-actions) for details.
