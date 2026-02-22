<?php

namespace Spatie\OgImage;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\LaravelScreenshot\Drivers\CloudflareDriver;
use Spatie\LaravelScreenshot\Drivers\ScreenshotDriver;
use Spatie\LaravelScreenshot\Facades\Screenshot;
use Spatie\OgImage\Exceptions\InvalidConfig;
use Spatie\OgImage\Support\TemplateExtractor;

class OgImageGenerator
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $actionClass
     * @return T
     */
    public static function getActionClass(string $actionName, string $actionClass): object
    {
        $configuredClass = config("og-image.actions.{$actionName}") ?? $actionClass;

        if (! is_a($configuredClass, $actionClass, true)) {
            throw InvalidConfig::invalidAction($actionName, $configuredClass, $actionClass);
        }

        return resolve($configuredClass);
    }

    protected ?Closure $fallbackUsing = null;

    protected ?Closure $resolveScreenshotUrlUsing = null;

    protected ?ScreenshotDriver $driver = null;

    protected ?Closure $configureScreenshotUsing = null;

    public function fallbackUsing(Closure $callback): self
    {
        $this->fallbackUsing = $callback;

        return $this;
    }

    public function getFallbackUsing(): ?Closure
    {
        return $this->fallbackUsing;
    }

    public function resolveScreenshotUrlUsing(Closure $callback): self
    {
        $this->resolveScreenshotUrlUsing = $callback;

        return $this;
    }

    public function resolveScreenshotUrl(): string
    {
        if ($this->resolveScreenshotUrlUsing) {
            return ($this->resolveScreenshotUrlUsing)(Request::instance());
        }

        return Request::url();
    }

    public function useDriver(ScreenshotDriver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function useCloudflare(string $apiToken, string $accountId): self
    {
        return $this->useDriver(new CloudflareDriver([
            'api_token' => $apiToken,
            'account_id' => $accountId,
        ]));
    }

    public function configureScreenshot(Closure $callback): self
    {
        $this->configureScreenshotUsing = $callback;

        return $this;
    }

    public function size(int $width, int $height): self
    {
        config()->set('og-image.width', $width);
        config()->set('og-image.height', $height);

        return $this;
    }

    public function format(string $format): self
    {
        config()->set('og-image.format', $format);

        return $this;
    }

    public function disk(string $disk, string $path = 'og-images'): self
    {
        config()->set('og-image.disk', $disk);
        config()->set('og-image.path', $path);

        return $this;
    }

    public function generate(string $url, string $path, ?int $width = null, ?int $height = null): void
    {
        $builder = Screenshot::url($url)
            ->size(
                $width ?? config('og-image.width', 1200),
                $height ?? config('og-image.height', 630),
            )
            ->disk(config('og-image.disk', 'public'), 'public');

        if ($quality = config('og-image.quality')) {
            $builder->quality($quality);
        }

        if ($this->driver) {
            $builder->setDriver($this->driver);
        }

        if ($this->configureScreenshotUsing) {
            ($this->configureScreenshotUsing)($builder);
        }

        $builder->save($path);
    }

    public function generateForUrl(string $pageUrl, ?string $format = null): string
    {
        $format ??= config('og-image.format', 'jpeg');
        $ogImage = app(OgImage::class);

        $html = Http::get($pageUrl)->body();
        $extracted = TemplateExtractor::extract($html);

        if ($extracted === null) {
            throw new RuntimeException("No OG image template found at {$pageUrl}");
        }

        $width = $extracted['width'];
        $height = $extracted['height'];
        $hash = $ogImage->hash($extracted['content'], $width, $height);
        $imagePath = $ogImage->imagePath($hash, $format);
        $disk = Storage::disk(config('og-image.disk', 'public'));

        if ($disk->exists($imagePath)) {
            return $disk->url($imagePath);
        }

        $ogImage->storeUrlInCache($hash, $pageUrl);

        if ($width !== null) {
            if ($height !== null) {
                $ogImage->storeDimensionsInCache($hash, $width, $height);
            }
        }

        $previewParameter = config('og-image.preview_parameter', 'ogimage');

        $this->generate("{$pageUrl}?{$previewParameter}", $imagePath, $width, $height);

        return $disk->url($imagePath);
    }
}
