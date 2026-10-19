<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\OgImage\Facades\OgImage as OgImageFacade;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

beforeEach(function () {
    $this->ogImage = app(OgImage::class);
});

it('can resolve the og image service', function () {
    expect(app(OgImage::class))->toBeInstanceOf(OgImage::class);
});

it('can resolve the og image generator', function () {
    expect(app(OgImageGenerator::class))->toBeInstanceOf(OgImageGenerator::class);
});

it('can hash html content', function () {
    $hash = $this->ogImage->hash('<div>Hello</div>');

    expect($hash)->toBe(md5('<div>Hello</div>'));
});

it('can generate template tag from html', function () {
    $result = $this->ogImage->html('<div>Hello</div>');

    $hash = md5('<div>Hello</div>');

    expect($result->toHtml())
        ->toContain("data-og-hash=\"{$hash}\"")
        ->toContain('data-og-format="jpeg"')
        ->toContain('<div>Hello</div></template>')
        ->not->toContain('<meta property="og:image"');
});

it('stores the current url in cache', function () {
    $this->ogImage->html('<div>Hello</div>');

    $hash = md5('<div>Hello</div>');

    expect(Cache::get("og-image:{$hash}"))->toBe(['url' => 'http://localhost']);
});

it('can store and retrieve from cache', function () {
    $this->ogImage->storeInCache('test-hash', 'https://example.com/my-page');

    expect($this->ogImage->getFromCache('test-hash'))->toBe(['url' => 'https://example.com/my-page']);
});

it('skips writing to cache when already cached', function () {
    $this->ogImage->storeInCache('test-hash', 'https://example.com/original-page', 800, 400);

    $this->ogImage->storeInCache('test-hash', 'https://example.com/my-page');

    expect($this->ogImage->getFromCache('test-hash'))->toBe([
        'url' => 'https://example.com/original-page',
        'width' => 800,
        'height' => 400,
    ]);
});

it('returns null when not in cache', function () {
    expect($this->ogImage->getFromCache('nonexistent'))->toBeNull();
});

it('can generate a url for a hash', function () {
    $url = $this->ogImage->url('abc123', 'png');

    expect($url)->toBe('https://example.com/og-image/abc123.png');
});

it('uses the configured format by default', function () {
    config()->set('og-image.format', 'webp');

    $url = $this->ogImage->url('abc123');

    expect($url)->toContain('abc123.webp');
});

it('can build the image path', function () {
    $path = $this->ogImage->imagePath('abc123', 'png');

    expect($path)->toBe('og-images/abc123.png');
});

it('uses the configured path', function () {
    config()->set('og-image.path', 'custom-og-images');

    $path = $this->ogImage->imagePath('abc123', 'png');

    expect($path)->toBe('custom-og-images/abc123.png');
});

it('uses Request::url() by default for resolveScreenshotUrl', function () {
    $url = app(OgImageGenerator::class)->resolveScreenshotUrl();

    expect($url)->toBe('http://localhost');
});

it('can customize url resolution with resolveScreenshotUrlUsing', function () {
    OgImageFacade::resolveScreenshotUrlUsing(function (Request $request) {
        return $request->fullUrl();
    });

    // Simulate a request with query params
    $this->get('/some-page?category=php');

    $url = app(OgImageGenerator::class)->resolveScreenshotUrl();

    expect($url)->toBe('http://localhost/some-page?category=php');
});

it('stores the resolved url in cache when using custom resolver', function () {
    OgImageFacade::resolveScreenshotUrlUsing(function (Request $request) {
        return $request->fullUrl();
    });

    $this->get('/some-page?category=php');

    $this->ogImage->html('<div>Hello</div>');

    $hash = md5('<div>Hello</div>');

    expect(Cache::get("og-image:{$hash}")['url'])->toBe('http://localhost/some-page?category=php');
});

it('produces a different hash when dimensions are provided', function () {
    $hashDefault = $this->ogImage->hash('<div>Hello</div>');
    $hashWithDimensions = $this->ogImage->hash('<div>Hello</div>', 800, 400);

    expect($hashDefault)->not->toBe($hashWithDimensions);
    expect($hashWithDimensions)->toBe(md5('<div>Hello</div>-800x400'));
});

it('produces the same hash without dimensions as before', function () {
    $hash = $this->ogImage->hash('<div>Hello</div>');

    expect($hash)->toBe(md5('<div>Hello</div>'));
});

it('can store and retrieve dimensions from cache', function () {
    $this->ogImage->storeInCache('test-hash', 'https://example.com/page', 800, 400);

    expect($this->ogImage->getFromCache('test-hash'))
        ->toBe(['url' => 'https://example.com/page', 'width' => 800, 'height' => 400]);
});

it('includes data attributes when dimensions are provided', function () {
    $result = $this->ogImage->html('<div>Hello</div>', width: 800, height: 400);

    expect($result->toHtml())
        ->toContain('data-og-width="800"')
        ->toContain('data-og-height="400"');
});

it('does not include data attributes without dimensions', function () {
    $result = $this->ogImage->html('<div>Hello</div>');

    expect($result->toHtml())
        ->not->toContain('data-og-width')
        ->not->toContain('data-og-height');
});

it('uses the route url in meta tags when image is not yet cached', function () {
    $hash = md5('<div>Hello</div>');

    $metaTags = $this->ogImage->metaTags($hash, 'jpeg');

    expect($metaTags->toHtml())
        ->toContain("og-image/{$hash}.jpeg");
});

it('always uses the route url in meta tags', function () {
    $hash = md5('<div>Hello</div>');

    $metaTags = $this->ogImage->metaTags($hash, 'jpeg');

    expect($metaTags->toHtml())
        ->toContain("og-image/{$hash}.jpeg");
});
