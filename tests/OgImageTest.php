<?php

use Illuminate\Support\Facades\Cache;
use Spatie\OgImage\OgImage;

beforeEach(function () {
    $this->ogImage = app(OgImage::class);
});

it('can hash html content', function () {
    $hash = $this->ogImage->hash('<div>Hello</div>');

    expect($hash)->toBe(md5('<div>Hello</div>'));
});

it('can generate meta tags from html', function () {
    $result = $this->ogImage->html('<div>Hello</div>');

    $hash = md5('<div>Hello</div>');

    expect($result->toHtml())
        ->toContain('<meta property="og:image"')
        ->toContain('<meta name="twitter:image"')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->toContain($hash.'.png');
});

it('stores html in cache', function () {
    $this->ogImage->html('<div>Hello</div>');

    $hash = md5('<div>Hello</div>');

    expect(Cache::get("og-image:{$hash}"))->toBe('<div>Hello</div>');
});

it('can retrieve html from cache', function () {
    $this->ogImage->storeHtmlInCache('test-hash', '<div>Hello</div>');

    expect($this->ogImage->getHtmlFromCache('test-hash'))->toBe('<div>Hello</div>');
});

it('returns null when html is not in cache', function () {
    expect($this->ogImage->getHtmlFromCache('nonexistent'))->toBeNull();
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

it('uses the configured base url', function () {
    config()->set('og-image.base_url', 'https://cdn.example.com');

    $url = $this->ogImage->url('abc123');

    expect($url)->toStartWith('https://cdn.example.com/og-image/');
});

it('uses the configured route prefix', function () {
    config()->set('og-image.route_prefix', 'social-images');

    $url = $this->ogImage->url('abc123');

    expect($url)->toContain('/social-images/');
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

it('can check if image exists on disk', function () {
    expect($this->ogImage->exists('nonexistent'))->toBeFalse();
});

it('uses a custom cache store when configured', function () {
    config()->set('og-image.cache_store', 'array');
    config()->set('cache.stores.array', ['driver' => 'array']);

    $this->ogImage->html('<div>Test</div>');

    $hash = md5('<div>Test</div>');

    expect(Cache::store('array')->get("og-image:{$hash}"))->toBe('<div>Test</div>');
});

it('can render meta tags from a view', function () {
    $this->app['view']->addNamespace('test', __DIR__.'/views');

    $result = $this->ogImage->view('test::og-template', ['title' => 'Hello']);

    expect($result->toHtml())
        ->toContain('<meta property="og:image"')
        ->toContain('.png');
});
