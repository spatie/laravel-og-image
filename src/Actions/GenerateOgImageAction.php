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

        $path = app(OgImage::class)->imagePath($hash, $format);
        $disk = Storage::disk(config('og-image.disk', 'public'));

        if (! $disk->exists($path)) {
            $cached = app(OgImage::class)->getFromCache($hash);

            if (! $cached) {
                abort(404);
            }

            $this->generateImage($cached, $path, $disk);
        }

        return $this->serveImage($disk, $path, $format);
    }

    protected function serveImage($disk, string $path, string $format): Response
    {
        $maxAge = config('og-image.redirect_cache_max_age', 60 * 60 * 24);

        $diskName = config('og-image.disk', 'public');
        $driver = config("filesystems.disks.{$diskName}.driver");

        if ($driver !== 'local') {
            return $this->redirectToImage($disk->url($path), $maxAge);
        }

        return $this->respondWithImage($disk, $path, $format, $maxAge);
    }

    protected function respondWithImage($disk, string $path, string $format, int $maxAge): Response
    {
        $mimeType = match ($format) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $headers = [
            'Content-Type' => $mimeType,
        ];

        if ($maxAge > 0) {
            $headers['Cache-Control'] = "public, max-age={$maxAge}";
        }

        return response($disk->get($path), 200, $headers);
    }

    protected function redirectToImage(string $url, int $maxAge): Response
    {
        $redirect = redirect($url, 301);

        if ($maxAge > 0) {
            $redirect->header('Cache-Control', "public, max-age={$maxAge}");
        }

        return $redirect;
    }

    protected function generateImage(array $cached, string $path, $disk): void
    {
        $lockTimeout = config('og-image.lock_timeout', 60);
        $pageUrl = $cached['url'];

        Cache::lock('og-image-generate:'.md5($pageUrl), $lockTimeout)->block($lockTimeout, function () use ($cached, $pageUrl, $path, $disk) {
            if ($disk->exists($path)) {
                return;
            }

            try {
                app(OgImageGenerator::class)->generate(
                    $pageUrl.'?'.config('og-image.preview_parameter', 'ogimage'),
                    $path,
                    $cached['width'] ?? null,
                    $cached['height'] ?? null,
                );
            } catch (Throwable $exception) {
                Log::error("OG image generation failed for {$pageUrl}: {$exception->getMessage()}");

                throw CouldNotGenerateOgImage::screenshotFailed($pageUrl, $exception);
            }
        });
    }
}
