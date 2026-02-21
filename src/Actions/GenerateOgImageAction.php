<?php

namespace Spatie\OgImage\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\Exceptions\CouldNotGenerateOgImage;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GenerateOgImageAction
{
    public function execute(string $filename): Response
    {
        $hash = pathinfo($filename, PATHINFO_FILENAME);
        $format = pathinfo($filename, PATHINFO_EXTENSION);

        if (! $hash || ! $format) {
            abort(404);
        }

        if ($cachedImageUrl = $this->getCachedImageUrl($hash, $format)) {
            return redirect($cachedImageUrl);
        }

        $pageUrl = app(OgImage::class)->getUrlFromCache($hash);

        if (! $pageUrl) {
            abort(404);
        }

        $this->generateImage($hash, $format, $pageUrl);

        return redirect($this->getCachedImageUrl($hash, $format));
    }

    protected function getCachedImageUrl(string $hash, string $format): ?string
    {
        return app(OgImage::class)->getImageUrlFromCache($hash, $format);
    }

    protected function generateImage(string $hash, string $format, string $pageUrl): void
    {
        $path = app(OgImage::class)->imagePath($hash, $format);
        $lockTimeout = config('og-image.lock_timeout', 60);
        $dimensions = app(OgImage::class)->getDimensionsFromCache($hash);

        Cache::lock("og-image-generate:{$hash}", $lockTimeout)->block($lockTimeout, function () use ($hash, $format, $pageUrl, $path, $dimensions) {
            if ($this->getCachedImageUrl($hash, $format)) {
                return;
            }

            try {
                app(OgImageGenerator::class)->generate(
                    $pageUrl.'?'.config('og-image.preview_parameter', 'ogimage'),
                    $path,
                    $dimensions['width'] ?? null,
                    $dimensions['height'] ?? null,
                );
            } catch (Throwable $exception) {
                Log::error("OG image generation failed for {$pageUrl}: {$exception->getMessage()}");

                throw CouldNotGenerateOgImage::screenshotFailed($pageUrl, $exception);
            }

            $disk = Storage::disk(config('og-image.disk', 'public'));
            app(OgImage::class)->storeImageUrlInCache($hash, $format, $disk->url($path));
        });
    }
}
