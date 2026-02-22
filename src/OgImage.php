<?php

namespace Spatie\OgImage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class OgImage
{
    public function html(string $html, ?string $format = null, ?int $width = null, ?int $height = null): HtmlString
    {
        $format ??= config('og-image.format', 'jpeg');
        $hash = $this->hash($html, $width, $height);

        $this->storeUrlInCache($hash, app(OgImageGenerator::class)->resolveScreenshotUrl());

        if ($width !== null) {
            if ($height !== null) {
                $this->storeDimensionsInCache($hash, $width, $height);
            }
        }

        $attributes = collect([
            'data-og-image' => true,
            'data-og-width' => $width,
            'data-og-height' => $height,
        ])
            ->filter()
            ->map(fn ($value, $key) => $value === true ? $key : "{$key}=\"{$value}\"")
            ->implode(' ');

        $template = "<template {$attributes}>{$html}</template>";

        return new HtmlString("{$template}".PHP_EOL.$this->metaTags($hash, $format));
    }

    public function url(string $hash, ?string $format = null): string
    {
        $format ??= config('og-image.format', 'jpeg');
        $baseUrl = rtrim(config('app.url'), '/');

        return "{$baseUrl}/og-image/{$hash}.{$format}";
    }

    public function hash(string $html, ?int $width = null, ?int $height = null): string
    {
        $subject = $width !== null && $height !== null
            ? "{$html}-{$width}x{$height}"
            : $html;

        return md5($subject);
    }

    public function storeUrlInCache(string $hash, string $url): void
    {
        if (Cache::has("og-image:{$hash}")) {
            return;
        }

        Cache::forever("og-image:{$hash}", $url);
    }

    public function getUrlFromCache(string $hash): ?string
    {
        return Cache::get("og-image:{$hash}");
    }

    public function storeDimensionsInCache(string $hash, int $width, int $height): void
    {
        if (Cache::has("og-image-dimensions:{$hash}")) {
            return;
        }

        Cache::forever("og-image-dimensions:{$hash}", compact('width', 'height'));
    }

    public function getDimensionsFromCache(string $hash): ?array
    {
        return Cache::get("og-image-dimensions:{$hash}");
    }

    public function imagePath(string $hash, string $format): string
    {
        $path = config('og-image.path', 'og-images');

        return "{$path}/{$hash}.{$format}";
    }

    public function metaTags(string $hash, string $format): HtmlString
    {
        $url = e($this->url($hash, $format));

        $tags = implode(PHP_EOL, [
            "<meta property=\"og:image\" content=\"{$url}\">",
            "<meta name=\"twitter:image\" content=\"{$url}\">",
            '<meta name="twitter:card" content="summary_large_image">',
        ]);

        return new HtmlString($tags);
    }
}
