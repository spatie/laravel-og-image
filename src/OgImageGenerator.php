<?php

namespace Spatie\OgImage;

use Closure;
use Spatie\LaravelScreenshot\Drivers\CloudflareDriver;
use Spatie\LaravelScreenshot\Facades\Screenshot;
use Spatie\LaravelScreenshot\ScreenshotBuilder;

class OgImageGenerator
{
    protected ?Closure $configureScreenshotUsing = null;

    public function useCloudflare(string $apiToken, string $accountId): self
    {
        $this->configureScreenshot(function (ScreenshotBuilder $screenshot) use ($apiToken, $accountId) {
            $screenshot->setDriver(new CloudflareDriver([
                'api_token' => $apiToken,
                'account_id' => $accountId,
            ]));
        });

        return $this;
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

        if ($this->configureScreenshotUsing) {
            ($this->configureScreenshotUsing)($builder);
        }

        $builder->save($path);
    }
}
