<?php

namespace Spatie\OgImage\Actions;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

class InjectOgImageFallbackAction
{
    public function execute(Request $request, string $content): ?string
    {
        $fallbackHtml = $this->renderFallback($request);

        if ($fallbackHtml === null) {
            return null;
        }

        $ogImage = app(OgImage::class);
        $hash = $ogImage->hash($fallbackHtml);
        $format = config('og-image.format', 'jpeg');

        $ogImage->storeUrlInCache($hash, app(OgImageGenerator::class)->resolveScreenshotUrl());

        $template = "<template data-og-image data-og-hash=\"{$hash}\" data-og-format=\"{$format}\">{$fallbackHtml}</template>";

        return $this->injectBeforeClosingTag($content, 'body', $template);
    }

    protected function renderFallback(Request $request): ?string
    {
        $fallback = app(OgImageGenerator::class)->getFallbackUsing();

        if ($fallback === null) {
            return null;
        }

        $view = $fallback($request);

        if ($view === null) {
            return null;
        }

        return $view instanceof View ? $view->render() : (string) $view;
    }

    protected function injectBeforeClosingTag(string $html, string $tag, string $inject): string
    {
        if (stripos($html, "</{$tag}>") === false) {
            return $html;
        }

        return str_ireplace("</{$tag}>", "{$inject}".PHP_EOL."</{$tag}>", $html);
    }
}
