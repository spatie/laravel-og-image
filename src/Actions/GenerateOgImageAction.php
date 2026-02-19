<?php

namespace Spatie\OgImage\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;
use Symfony\Component\HttpFoundation\Response;

class GenerateOgImageAction
{
    public function execute(string $filename): Response
    {
        $hash = $this->parseHash($filename);

        $format = $this->parseFormat($filename);

        if (! $hash || ! $format) {
            abort(404);
        }

        $cachedImageUrl = $this->getCachedImageUrl($hash, $format);

        if ($cachedImageUrl) {
            return redirect($cachedImageUrl);
        }

        $pageUrl = $this->getPageUrl($hash);

        if (! $pageUrl) {
            abort(404);
        }

        $this->generateImage($hash, $format, $pageUrl);

        return redirect($this->getCachedImageUrl($hash, $format));
    }

    protected function parseHash(string $filename): string
    {
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    protected function parseFormat(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    protected function getCachedImageUrl(string $hash, string $format): ?string
    {
        return app(OgImage::class)->getImageUrlFromCache($hash, $format);
    }

    protected function getPageUrl(string $hash): ?string
    {
        return app(OgImage::class)->getUrlFromCache($hash);
    }

    protected function generateImage(string $hash, string $format, string $pageUrl): void
    {
        $path = $this->imagePath($hash, $format);

        Cache::lock("og-image-generate:{$hash}", 60)->block(60, function () use ($hash, $format, $pageUrl, $path) {
            if ($this->getCachedImageUrl($hash, $format)) {
                return;
            }

            $this->takeScreenshot($pageUrl, $path, $format);

            $this->cacheImageUrl($hash, $format, $path);
        });
    }

    protected function imagePath(string $hash, string $format): string
    {
        return app(OgImage::class)->imagePath($hash, $format);
    }

    protected function takeScreenshot(string $pageUrl, string $path, string $format): void
    {
        app(OgImageGenerator::class)->generate("{$pageUrl}?ogimage", $path, $format);
    }

    protected function cacheImageUrl(string $hash, string $format, string $path): void
    {
        $disk = Storage::disk(config('og-image.disk', 'public'));

        app(OgImage::class)->storeImageUrlInCache($hash, $format, $disk->url($path));
    }
}
