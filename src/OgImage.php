<?php

namespace Spatie\OgImage;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class OgImage
{
    public function view(string $view, array $data = [], ?string $format = null): HtmlString
    {
        $html = view($view, $data)->render();

        return $this->html($html, $format);
    }

    public function html(string $html, ?string $format = null): HtmlString
    {
        $format ??= config('og-image.format', 'png');
        $hash = $this->hash($html);

        $this->storeHtmlInCache($hash, $html);

        return $this->metaTags($hash, $format);
    }

    public function url(string $hash, ?string $format = null): string
    {
        $format ??= config('og-image.format', 'png');
        $baseUrl = config('og-image.base_url') ?? config('app.url');
        $prefix = config('og-image.route_prefix', 'og-image');

        return rtrim($baseUrl, '/').'/'.$prefix.'/'.$hash.'.'.$format;
    }

    public function exists(string $hash, ?string $format = null): bool
    {
        $format ??= config('og-image.format', 'png');

        return Storage::disk(config('og-image.disk', 'public'))
            ->exists($this->imagePath($hash, $format));
    }

    public function hash(string $html): string
    {
        return md5($html);
    }

    public function storeHtmlInCache(string $hash, string $html): void
    {
        $ttl = config('og-image.cache_ttl');

        if ($ttl === null) {
            $this->cacheStore()->forever("og-image:{$hash}", $html);

            return;
        }

        $this->cacheStore()->put("og-image:{$hash}", $html, $ttl);
    }

    public function getHtmlFromCache(string $hash): ?string
    {
        return $this->cacheStore()->get("og-image:{$hash}");
    }

    public function imagePath(string $hash, string $format): string
    {
        $path = config('og-image.path', 'og-images');

        return $path.'/'.$hash.'.'.$format;
    }

    protected function metaTags(string $hash, string $format): HtmlString
    {
        $url = $this->url($hash, $format);

        $tags = implode(PHP_EOL, [
            '<meta property="og:image" content="'.e($url).'">',
            '<meta name="twitter:image" content="'.e($url).'">',
            '<meta name="twitter:card" content="summary_large_image">',
        ]);

        return new HtmlString($tags);
    }

    protected function cacheStore(): Repository
    {
        $store = config('og-image.cache_store');

        return Cache::store($store);
    }
}
