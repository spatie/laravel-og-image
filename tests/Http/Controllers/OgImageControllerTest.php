<?php

use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\OgImage;

beforeEach(function () {
    Storage::fake('public');
});

it('returns 404 when image does not exist and url is not in cache', function () {
    $this->get('/og-image/nonexistent.png')->assertNotFound();
});

it('serves the image directly for png', function () {
    $ogImage = app(OgImage::class);
    $ogImage->storeUrlInCache('abc123', 'https://example.com/page');

    Storage::disk('public')->put('og-images/abc123.png', 'fake-png-content');

    $response = $this->get('/og-image/abc123.png');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->getContent())->toBe('fake-png-content');
});

it('serves the image directly for jpeg', function () {
    Storage::disk('public')->put('og-images/abc123.jpeg', 'fake-jpeg-content');

    $response = $this->get('/og-image/abc123.jpeg');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');
    expect($response->getContent())->toBe('fake-jpeg-content');
});

it('serves the image directly for webp', function () {
    Storage::disk('public')->put('og-images/abc123.webp', 'fake-webp-content');

    $response = $this->get('/og-image/abc123.webp');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/webp');
    expect($response->getContent())->toBe('fake-webp-content');
});

it('includes cache-control headers', function () {
    Storage::disk('public')->put('og-images/abc123.jpeg', 'fake-jpeg-content');

    $response = $this->get('/og-image/abc123.jpeg');

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=86400');
});

it('rejects invalid filenames', function () {
    $this->get('/og-image/invalid-chars.png')->assertNotFound();
    $this->get('/og-image/abc123.gif')->assertNotFound();
    $this->get('/og-image/abc123')->assertNotFound();
});
