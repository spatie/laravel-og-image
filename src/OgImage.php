<?php

namespace Spatie\OgImage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\HtmlString;

class OgImage
{
    public function html(string $html, ?string $format = null): HtmlString
    {
        $format ??= config('og-image.format', 'jpeg');
        $hash = $this->hash($html);

        $this->storeUrlInCache($hash, Request::url());

        $template = '<template data-og-image>'.$html.'</template>';

        return new HtmlString($template.PHP_EOL.$this->metaTags($hash, $format));
    }

    public function url(string $hash, ?string $format = null): string
    {
        $format ??= config('og-image.format', 'jpeg');

        return rtrim(config('app.url'), '/').'/og-image/'.$hash.'.'.$format;
    }

    public function hash(string $html): string
    {
        return md5($html);
    }

    public function storeUrlInCache(string $hash, string $url): void
    {
        Cache::forever("og-image:{$hash}", $url);
    }

    public function getUrlFromCache(string $hash): ?string
    {
        return Cache::get("og-image:{$hash}");
    }

    public function storeImageUrlInCache(string $hash, string $format, string $url): void
    {
        Cache::forever("og-image-url:{$hash}.{$format}", $url);
    }

    public function getImageUrlFromCache(string $hash, string $format): ?string
    {
        return Cache::get("og-image-url:{$hash}.{$format}");
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
}
