<?php

namespace Spatie\OgImage\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Spatie\OgImage\Actions\InjectOgImageFallbackAction;
use Spatie\OgImage\Actions\RenderOgImageScreenshotAction;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RenderOgImageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($response)) {
            return $response;
        }

        $content = $response->getContent();
        $isPreviewRequest = $request->has(config('og-image.preview_parameter', 'ogimage'));
        $hasTemplate = str_contains($content, '<template data-og-image');

        if (! $isPreviewRequest && ! $hasTemplate && ! app(OgImageGenerator::class)->getFallbackUsing()) {
            return $response;
        }

        if (! $hasTemplate) {
            $content = $this->injectFallback($request, $content);
        }

        if ($content !== null) {
            $content = $this->injectMetaTagsInHead($content);
            $original = $response instanceof IlluminateResponse ? $response->original : null;
            $response->setContent($content);

            if ($response instanceof IlluminateResponse) {
                $response->original = $original;
            }
        }

        if ($isPreviewRequest) {
            $this->renderScreenshotIfNeeded($response);
        }

        return $response;
    }

    protected function shouldSkip(Response $response): bool
    {
        if ($response instanceof JsonResponse) {
            return true;
        }

        if ($response instanceof BinaryFileResponse) {
            return true;
        }

        if ($response instanceof StreamedResponse) {
            return true;
        }

        if (! is_string($response->getContent())) {
            return true;
        }

        return false;
    }

    protected function injectFallback(Request $request, string $content): ?string
    {
        $fallbackAction = OgImageGenerator::getActionClass('inject_og_image_fallback', InjectOgImageFallbackAction::class);

        return $fallbackAction->execute($request, $content) ?? $content;
    }

    protected function injectMetaTagsInHead(string $content): string
    {
        if (! preg_match('/data-og-hash="([a-f0-9]+)"/', $content, $hashMatch)) {
            return $content;
        }

        $hash = $hashMatch[1];

        preg_match('/data-og-format="(\w+)"/', $content, $formatMatch);
        $format = $formatMatch[1] ?? app(OgImage::class)->defaultFormat();

        $metaTags = app(OgImage::class)->metaTags($hash, $format)->toHtml();

        return str_ireplace('</head>', $metaTags.PHP_EOL.'</head>', $content);
    }

    protected function renderScreenshotIfNeeded(Response $response): void
    {
        $screenshotAction = OgImageGenerator::getActionClass('render_og_image_screenshot', RenderOgImageScreenshotAction::class);

        $html = $screenshotAction->execute($response->getContent());

        if ($html === null) {
            return;
        }

        $response->setContent($html);
    }
}
