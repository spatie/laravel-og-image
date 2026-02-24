<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\OgImage\Facades\OgImage;
use Spatie\OgImage\Http\Middleware\RenderOgImageMiddleware;

beforeEach(function () {
    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/test-page', function () {
        return response(<<<'HTML'
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <link rel="stylesheet" href="/css/app.css">
        </head>
        <body>
            <h1>My Page</h1>
            <template data-og-image data-og-hash="abc123def456" data-og-format="jpeg"><div class="og-content">Hello OG</div></template>
        </body>
        </html>
        HTML);
    });
});

it('injects meta tags into head for pages with template', function () {
    $response = $this->get('/test-page');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)
        ->toContain('<template data-og-image')
        ->toContain('<meta property="og:image"')
        ->toContain('<meta name="twitter:image"')
        ->toContain('<meta name="twitter:card" content="summary_large_image">');

    // Meta tags should be in the head, not in the body
    $headEnd = stripos($content, '</head>');
    $ogImagePos = strpos($content, '<meta property="og:image"');

    expect($ogImagePos)->toBeLessThan($headEnd);
});

it('renders only the template content with ogimage parameter', function () {
    $response = $this->get('/test-page?ogimage');

    $response->assertOk();

    $content = $response->getContent();

    expect($content)
        ->toContain('<div class="og-content">Hello OG</div>')
        ->toContain('width: 1200px')
        ->toContain('height: 630px')
        ->not->toContain('<h1>My Page</h1>')
        ->not->toContain('<template data-og-image');
});

it('preserves the head content from the original page', function () {
    $response = $this->get('/test-page?ogimage');

    $content = $response->getContent();

    expect($content)
        ->toContain('<link rel="stylesheet" href="/css/app.css">')
        ->toContain('<meta charset="utf-8">');
});

it('adds css reset styles', function () {
    $response = $this->get('/test-page?ogimage');

    $content = $response->getContent();

    expect($content)
        ->toContain('box-sizing: border-box')
        ->toContain('margin: 0')
        ->toContain('overflow: hidden');
});

it('uses configured dimensions', function () {
    config()->set('og-image.width', 800);
    config()->set('og-image.height', 400);

    $response = $this->get('/test-page?ogimage');

    $content = $response->getContent();

    expect($content)
        ->toContain('width: 800px')
        ->toContain('height: 400px');
});

it('returns the original response when no template tag is found', function () {
    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><body><h1>No OG image here</h1></body></html>');
    });

    $response = $this->get('/no-template?ogimage');

    expect($response->getContent())->toContain('<h1>No OG image here</h1>');
});

it('injects fallback template and meta tags when no template exists', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return view('test-fallback', ['title' => 'Fallback Title']);
    });

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><head><meta charset="utf-8"></head><body><h1>Page</h1></body></html>');
    });

    $response = $this->get('/no-template');

    $content = $response->getContent();

    expect($content)
        ->toContain('<template data-og-image')
        ->toContain('data-og-hash=')
        ->toContain('Fallback Title')
        ->toContain('<meta property="og:image"')
        ->toContain('<meta name="twitter:image"')
        ->toContain('<meta name="twitter:card" content="summary_large_image">');

    // Meta tags should be in the head
    $headEnd = stripos($content, '</head>');
    $ogImagePos = strpos($content, '<meta property="og:image"');

    expect($ogImagePos)->toBeLessThan($headEnd);
});

it('renders the fallback template with ogimage parameter', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return view('test-fallback', ['title' => 'Fallback Title']);
    });

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><head><meta charset="utf-8"></head><body><h1>Page</h1></body></html>');
    });

    $response = $this->get('/no-template?ogimage');

    $content = $response->getContent();

    expect($content)
        ->toContain('Fallback Title')
        ->toContain('width: 1200px')
        ->toContain('height: 630px')
        ->not->toContain('<h1>Page</h1>');
});

it('caches the fallback url', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return view('test-fallback', ['title' => 'Fallback Title']);
    });

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><head></head><body><h1>Page</h1></body></html>');
    });

    $this->get('/no-template');

    $html = view('test-fallback', ['title' => 'Fallback Title'])->render();
    $hash = md5($html);

    expect(Cache::get("og-image:{$hash}"))->toBe(['url' => 'http://localhost/no-template']);
});

it('does not inject fallback when page has a template', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return view('test-fallback', ['title' => 'Should Not Appear']);
    });

    $response = $this->get('/test-page');

    expect($response->getContent())->not->toContain('Should Not Appear');
});

it('skips fallback when closure returns null', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return null;
    });

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><head></head><body><h1>Page</h1></body></html>');
    });

    $response = $this->get('/no-template');

    expect($response->getContent())
        ->not->toContain('<template data-og-image')
        ->not->toContain('og:image');
});

it('does not inject fallback when no fallback is registered', function () {
    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/no-template', function () {
        return response('<html><head></head><body><h1>Page</h1></body></html>');
    });

    $response = $this->get('/no-template');

    expect($response->getContent())
        ->not->toContain('<template data-og-image')
        ->not->toContain('og:image');
});

it('detects template tags with custom dimension attributes', function () {
    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/custom-size', function () {
        return response(<<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body>
            <template data-og-image data-og-hash="customhash123" data-og-format="jpeg" data-og-width="800" data-og-height="400"><div>Custom</div></template>
        </body>
        </html>
        HTML);
    });

    $response = $this->get('/custom-size');

    $response->assertOk();
    expect($response->getContent())->toContain('<template data-og-image');
});

it('renders custom dimensions in screenshot mode', function () {
    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/custom-size', function () {
        return response(<<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body>
            <template data-og-image data-og-hash="customhash123" data-og-format="jpeg" data-og-width="800" data-og-height="400"><div>Custom</div></template>
        </body>
        </html>
        HTML);
    });

    $response = $this->get('/custom-size?ogimage');

    $content = $response->getContent();

    expect($content)
        ->toContain('width: 800px')
        ->toContain('height: 400px')
        ->toContain('<div>Custom</div>');
});

it('does not cache fallback url for non-html responses without a body tag', function () {
    OgImage::fallbackUsing(function (Request $request) {
        return view('test-fallback', ['title' => 'Fallback Title']);
    });

    Route::middleware(['web', RenderOgImageMiddleware::class])->get('/feed.xml', function () {
        return response('<?xml version="1.0" encoding="UTF-8"?><rss><channel><title>Feed</title></channel></rss>', 200, [
            'Content-Type' => 'application/xml',
        ]);
    });

    $this->get('/feed.xml');

    $html = view('test-fallback', ['title' => 'Fallback Title'])->render();
    $hash = md5($html);

    expect(Cache::has("og-image:{$hash}"))->toBeFalse();
});
