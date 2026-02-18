<?php

namespace Spatie\OgImage\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;
use Spatie\OgImage\FakeOgImage;

/**
 * @method static HtmlString view(string $view, array $data = [], ?string $format = null)
 * @method static HtmlString html(string $html, ?string $format = null)
 * @method static string url(string $hash, ?string $format = null)
 * @method static bool exists(string $hash, ?string $format = null)
 * @method static string hash(string $html)
 *
 * @see \Spatie\OgImage\OgImage
 */
class OgImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Spatie\OgImage\OgImage::class;
    }

    public static function fake(): FakeOgImage
    {
        $fake = new FakeOgImage;

        static::swap($fake);

        return $fake;
    }
}
