<?php

use Spatie\OgImage\Facades\OgImage;

it('can fake og image and assert view was rendered', function () {
    $fake = OgImage::fake();

    $this->app['view']->addNamespace('test', __DIR__.'/views');

    OgImage::view('test::og-template', ['title' => 'Hello']);

    $fake->assertViewRendered('test::og-template');
});

it('can assert view rendered with callback', function () {
    $fake = OgImage::fake();

    $this->app['view']->addNamespace('test', __DIR__.'/views');

    OgImage::view('test::og-template', ['title' => 'Hello World']);

    $fake->assertViewRendered(function (string $view, array $data) {
        return $view === 'test::og-template' && $data['title'] === 'Hello World';
    });
});

it('can assert html was rendered', function () {
    $fake = OgImage::fake();

    OgImage::html('<div>Hello</div>');

    $fake->assertHtmlRendered('<div>Hello</div>');
});

it('can assert html rendered with callback', function () {
    $fake = OgImage::fake();

    OgImage::html('<div>Hello World</div>');

    $fake->assertHtmlRendered(fn (string $html) => str_contains($html, 'Hello World'));
});

it('can assert nothing was rendered', function () {
    $fake = OgImage::fake();

    $fake->assertNothingRendered();
});

it('returns meta tags from fake', function () {
    OgImage::fake();

    $result = OgImage::html('<div>Hello</div>');

    expect($result->toHtml())
        ->toContain('<meta property="og:image"')
        ->toContain('<meta name="twitter:image"')
        ->toContain('<meta name="twitter:card" content="summary_large_image">');
});
