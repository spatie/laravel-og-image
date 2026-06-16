<?php

namespace Spatie\OgImage\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Spatie\LaravelScreenshot\Drivers\ScreenshotDriver;
use Spatie\OgImage\OgImageGenerator;

/**
 * @method static OgImageGenerator fallbackUsing(Closure $callback)
 * @method static OgImageGenerator resolveScreenshotUrlUsing(Closure $callback)
 * @method static OgImageGenerator useDriver(ScreenshotDriver $driver)
 * @method static OgImageGenerator useCloudflare(string $apiToken, string $accountId)
 * @method static OgImageGenerator configureScreenshot(Closure $callback)
 * @method static OgImageGenerator size(int $width, int $height)
 * @method static OgImageGenerator format(string $format)
 * @method static OgImageGenerator disk(string $disk, string $path = 'og-images')
 * @method static string generateForUrl(string $pageUrl, ?string $format = null)
 * @method static object getActionClass(string $actionName, string $actionClass)
 *
 * @see OgImageGenerator
 */
class OgImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OgImageGenerator::class;
    }
}
