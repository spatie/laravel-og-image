<?php

namespace Spatie\OgImage;

use Closure;
use Illuminate\Support\Facades\Request;
use Spatie\LaravelScreenshot\Drivers\CloudflareDriver;
use Spatie\LaravelScreenshot\Drivers\ScreenshotDriver;
use Spatie\LaravelScreenshot\Facades\Screenshot;

class OgImageGenerator
{
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

    public function generate(string $url, string $path, string $format): void
    {
        $width = config('og-image.width', 1200);
        $height = config('og-image.height', 630);
        $diskName = config('og-image.disk', 'public');

        $builder = Screenshot::url($url)
            ->size($width, $height)
            ->disk($diskName, 'public');

        if ($this->driver) {
            $builder->setDriver($this->driver);
        }

        if ($this->configureScreenshotUsing) {
            ($this->configureScreenshotUsing)($builder);
        }

        $builder->save($path);
    }
}
