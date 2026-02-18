<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('returns 404 when image does not exist and html is not in cache', function () {
    $this->get('/og-image/nonexistent.png')->assertNotFound();
});

it('serves an existing image from disk', function () {
    Storage::disk('public')->put('og-images/abc123.png', 'fake-image-content');

    $response = $this->get('/og-image/abc123.png');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable');
    expect($response->getContent())->toBe('fake-image-content');
});

it('serves jpeg images with correct content type', function () {
    Storage::disk('public')->put('og-images/abc123.jpeg', 'fake-jpeg-content');

    $response = $this->get('/og-image/abc123.jpeg');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');
});

it('serves webp images with correct content type', function () {
    Storage::disk('public')->put('og-images/abc123.webp', 'fake-webp-content');

    $response = $this->get('/og-image/abc123.webp');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/webp');
});

it('rejects invalid filenames', function () {
    $this->get('/og-image/invalid-chars.png')->assertNotFound();
    $this->get('/og-image/abc123.gif')->assertNotFound();
    $this->get('/og-image/abc123')->assertNotFound();
});
