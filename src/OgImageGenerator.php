<?php

namespace Spatie\OgImage;

use Spatie\LaravelScreenshot\Facades\Screenshot;

class OgImageGenerator
{
    public function generate(string $html, string $path, string $format): void
    {
        $width = config('og-image.width', 1200);
        $height = config('og-image.height', 630);
        $headTags = config('og-image.head', []);
        $diskName = config('og-image.disk', 'public');
        $screenshotConfig = config('og-image.screenshot', []);

        /** @var \Illuminate\View\View $documentView */
        $documentView = view('og-image::document', [
            'html' => $html,
            'headTags' => $headTags,
            'width' => $width,
            'height' => $height,
        ]);

        $fullHtml = $documentView->render();

        $builder = Screenshot::html($fullHtml)
            ->size($width, $height)
            ->disk($diskName, 'public');

        if (isset($screenshotConfig['device_scale_factor'])) {
            $builder->deviceScaleFactor($screenshotConfig['device_scale_factor']);
        }

        if (isset($screenshotConfig['wait_until'])) {
            $builder->waitUntil($screenshotConfig['wait_until']);
        }

        if (isset($screenshotConfig['wait_for_timeout'])) {
            $builder->waitForTimeout($screenshotConfig['wait_for_timeout']);
        }

        $builder->save($path);
    }
}
