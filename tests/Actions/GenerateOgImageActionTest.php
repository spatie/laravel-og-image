<?php

use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\Actions\GenerateOgImageAction;
use Spatie\OgImage\Exceptions\CouldNotGenerateOgImage;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

beforeEach(function () {
    Storage::fake('public');
});

it('uses the configured lock timeout', function () {
    config()->set('og-image.lock_timeout', 30);

    $ogImage = app(OgImage::class);
    $ogImage->storeUrlInCache('abc123', 'https://example.com/page');

    $mockGenerator = Mockery::mock(OgImageGenerator::class);
    $mockGenerator->shouldReceive('generate')
        ->once()
        ->withArgs(function ($url, $path, $width, $height) {
            return str_contains($url, '?ogimage');
        });

    app()->instance(OgImageGenerator::class, $mockGenerator);

    $action = app(GenerateOgImageAction::class);
    $action->execute('abc123.jpeg');
});

it('throws CouldNotGenerateOgImage when screenshot fails', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeUrlInCache('abc123', 'https://example.com/page');

    $mockGenerator = Mockery::mock(OgImageGenerator::class);
    $mockGenerator->shouldReceive('generate')
        ->once()
        ->andThrow(new RuntimeException('Chrome not found'));

    app()->instance(OgImageGenerator::class, $mockGenerator);

    $action = app(GenerateOgImageAction::class);
    $action->execute('abc123.jpeg');
})->throws(CouldNotGenerateOgImage::class, 'Could not generate OG image');

it('passes cached dimensions to the generator', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeUrlInCache('abc123', 'https://example.com/page');
    $ogImage->storeDimensionsInCache('abc123', 800, 400);

    $mockGenerator = Mockery::mock(OgImageGenerator::class);
    $mockGenerator->shouldReceive('generate')
        ->once()
        ->withArgs(function ($url, $path, $width, $height) {
            return $width === 800 && $height === 400;
        });

    app()->instance(OgImageGenerator::class, $mockGenerator);

    $action = app(GenerateOgImageAction::class);
    $action->execute('abc123.jpeg');
});

it('passes null dimensions when none are cached', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeUrlInCache('abc123', 'https://example.com/page');

    $mockGenerator = Mockery::mock(OgImageGenerator::class);
    $mockGenerator->shouldReceive('generate')
        ->once()
        ->withArgs(function ($url, $path, $width, $height) {
            return $width === null && $height === null;
        });

    app()->instance(OgImageGenerator::class, $mockGenerator);

    $action = app(GenerateOgImageAction::class);
    $action->execute('abc123.jpeg');
});
