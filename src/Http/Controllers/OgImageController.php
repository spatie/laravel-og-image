<?php

namespace Spatie\OgImage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

class OgImageController
{
    public function __invoke(Request $request, string $filename): mixed
    {
        $parts = explode('.', $filename, 2);

        if (count($parts) !== 2) {
            abort(404);
        }

        [$hash, $format] = $parts;

        $ogImage = app(OgImage::class);
        $disk = Storage::disk(config('og-image.disk', 'public'));
        $path = $ogImage->imagePath($hash, $format);

        if ($disk->exists($path)) {
            return $this->serveImage($disk, $path, $format);
        }

        $html = $ogImage->getHtmlFromCache($hash);

        if ($html === null) {
            abort(404);
        }

        app(OgImageGenerator::class)->generate($html, $path, $format);

        return $this->serveImage($disk, $path, $format);
    }

    protected function serveImage(mixed $disk, string $path, string $format): mixed
    {
        $mimeTypes = [
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];

        $mimeType = $mimeTypes[$format] ?? 'image/png';

        return response($disk->get($path), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
