<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\Http\Middleware\RenderOgImageMiddleware;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

beforeEach(function () {
    Storage::fake('public');

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/test-page', function () {
        $ogHtml = app(OgImage::class)->html('<div>Hello OG</div>');

        return response("<!DOCTYPE html><html><head></head><body><h1>Page</h1>{$ogHtml}</body></html>");
    });
});

it('creates an og image when visiting the og image url', function () {
    $response = $this->get('/test-page');
    $response->assertOk();

    $content = $response->getContent();

    expect($content)
        ->toContain('<template data-og-image>')
        ->toContain('<meta property="og:image"');

    preg_match('/og-image\/([a-f0-9]+)\.jpeg/', $content, $matches);
    $hash = $matches[1];

    expect(Cache::get("og-image:{$hash}"))->toBe('http://localhost/test-page');

    $mockGenerator = Mockery::mock(OgImageGenerator::class);
    $mockGenerator->shouldReceive('generate')
        ->once()
        ->andReturnUsing(function ($url, $path) {
            expect($url)->toContain('?ogimage');

            Storage::disk('public')->put($path, 'fake-jpeg-content');
        });
    app()->instance(OgImageGenerator::class, $mockGenerator);

    $response = $this->get("/og-image/{$hash}.jpeg");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');

    Storage::disk('public')->assertExists("og-images/{$hash}.jpeg");
});

it('serves the image directly when it already exists on disk', function () {
    $response = $this->get('/test-page');

    preg_match('/og-image\/([a-f0-9]+)\.jpeg/', $response->getContent(), $matches);
    $hash = $matches[1];

    Storage::disk('public')->put("og-images/{$hash}.jpeg", 'fake-jpeg-content');

    $response = $this->get("/og-image/{$hash}.jpeg");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');
    expect($response->getContent())->toBe('fake-jpeg-content');
});
