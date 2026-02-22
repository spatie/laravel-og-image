<?php

namespace Spatie\OgImage\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\OgImage\Actions\InjectOgImageFallbackAction;
use Spatie\OgImage\Actions\RenderOgImageScreenshotAction;
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

        $isPreviewRequest = $request->has(config('og-image.preview_parameter', 'ogimage'));

        if (! $isPreviewRequest && ! app(OgImageGenerator::class)->getFallbackUsing()) {
            return $response;
        }

        $this->injectFallbackIfNeeded($request, $response, $response->getContent());

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

    protected function injectFallbackIfNeeded(Request $request, Response $response, string $content): void
    {
        if (str_contains($content, '<template data-og-image')) {
            return;
        }

        $fallbackAction = OgImageGenerator::getActionClass('inject_og_image_fallback', InjectOgImageFallbackAction::class);

        $content = $fallbackAction->execute($request, $content);

        if ($content === null) {
            return;
        }

        $response->setContent($content);
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
