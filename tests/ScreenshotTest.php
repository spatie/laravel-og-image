<?php

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelScreenshot\Facades\Screenshot;
use Spatie\LaravelScreenshot\ScreenshotServiceProvider;

beforeEach(function () {
    app()->register(ScreenshotServiceProvider::class);

    Storage::fake('public');
});

it('can generate a real screenshot from html', function () {
    $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><style>*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; } html, body { width: 1200px; height: 630px; overflow: hidden; }</style></head>
        <body>
            <div style="width: 1200px; height: 630px; display: flex; align-items: center; justify-content: center; background: #1a1a2e; color: white; font-family: sans-serif;">
                <h1 style="font-size: 48px;">OG Image Test</h1>
            </div>
        </body>
        </html>
    HTML;

    Screenshot::html($html)
        ->size(1200, 630)
        ->deviceScaleFactor(1)
        ->withBrowsershot(fn (Browsershot $browsershot) => $browsershot->noSandbox())
        ->disk(config('og-image.disk', 'public'), 'public')
        ->save('og-images/test.jpeg');

    Storage::disk('public')->assertExists('og-images/test.jpeg');

    $imagePath = Storage::disk('public')->path('og-images/test.jpeg');
    $imageSize = getimagesize($imagePath);

    expect($imageSize[0])->toBe(1200);
    expect($imageSize[1])->toBe(630);
    expect($imageSize['mime'])->toBe('image/jpeg');
})->group('screenshot');

it('can generate a screenshot with custom dimensions', function () {
    $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><style>*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; } html, body { width: 800px; height: 400px; overflow: hidden; }</style></head>
        <body>
            <div style="width: 800px; height: 400px; background: #4361ee; color: white; font-family: sans-serif; display: flex; align-items: center; justify-content: center;">
                <h1>Custom Size</h1>
            </div>
        </body>
        </html>
    HTML;

    Screenshot::html($html)
        ->size(800, 400)
        ->deviceScaleFactor(1)
        ->withBrowsershot(fn (Browsershot $browsershot) => $browsershot->noSandbox())
        ->disk(config('og-image.disk', 'public'), 'public')
        ->save('og-images/custom.jpeg');

    Storage::disk('public')->assertExists('og-images/custom.jpeg');

    $imagePath = Storage::disk('public')->path('og-images/custom.jpeg');
    $imageSize = getimagesize($imagePath);

    expect($imageSize[0])->toBe(800);
    expect($imageSize[1])->toBe(400);
})->group('screenshot');
