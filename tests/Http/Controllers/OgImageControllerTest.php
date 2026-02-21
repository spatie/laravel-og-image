<?php

use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\OgImage;

beforeEach(function () {
    Storage::fake('public');
});

it('returns 404 when image does not exist and url is not in cache', function () {
    $this->get('/og-image/nonexistent.png')->assertNotFound();
});

it('redirects to the cached image url', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeImageUrlInCache('abc123', 'png', 'https://example.com/og-images/abc123.png');

    $response = $this->get('/og-image/abc123.png');

    $response->assertRedirect('https://example.com/og-images/abc123.png');
});

it('redirects to cached jpeg image url', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeImageUrlInCache('abc123', 'jpeg', 'https://example.com/og-images/abc123.jpeg');

    $response = $this->get('/og-image/abc123.jpeg');

    $response->assertRedirect('https://example.com/og-images/abc123.jpeg');
});

it('redirects to cached webp image url', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeImageUrlInCache('abc123', 'webp', 'https://example.com/og-images/abc123.webp');

    $response = $this->get('/og-image/abc123.webp');

    $response->assertRedirect('https://example.com/og-images/abc123.webp');
});

it('rejects invalid filenames', function () {
    $this->get('/og-image/invalid-chars.png')->assertNotFound();
    $this->get('/og-image/abc123.gif')->assertNotFound();
    $this->get('/og-image/abc123')->assertNotFound();
});
