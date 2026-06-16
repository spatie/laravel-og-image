<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\OgImage\OgImage;
use Spatie\OgImage\OgImageGenerator;

beforeEach(function () {
    Storage::fake('public');
});

it('appends the preview parameter to a url without query parameters', function () {
    $pageUrl = 'https://example.com/page';

    fakePageWithOgTemplate($pageUrl);

    $generator = partialGeneratorExpectingPreviewUrl(function ($url) {
        return str_contains((string) $url, 'https://example.com/page?ogimage');
    });

    $generator->generateForUrl($pageUrl);
});

it('appends the preview parameter to a url with existing query parameters', function () {
    $pageUrl = 'https://example.com/page?foo=bar';

    fakePageWithOgTemplate($pageUrl);

    $generator = partialGeneratorExpectingPreviewUrl(function ($url) {
        $url = (string) $url;

        return str_contains($url, '?foo=bar&ogimage')
            && ! str_contains($url, '?foo=bar?ogimage');
    });

    $generator->generateForUrl($pageUrl);
});

function fakePageWithOgTemplate(string $pageUrl): void
{
    $ogHtml = app(OgImage::class)->html('<div>Hello OG</div>');

    Http::fake([
        $pageUrl => Http::response("<html><head></head><body>{$ogHtml}</body></html>"),
    ]);
}

function partialGeneratorExpectingPreviewUrl(Closure $assertUrl): OgImageGenerator
{
    $generator = Mockery::mock(OgImageGenerator::class)->makePartial();

    $generator->shouldReceive('generate')
        ->once()
        ->withArgs(fn ($url) => $assertUrl($url))
        ->andReturnUsing(function ($url, $path) {
            Storage::disk('public')->put($path, 'fake-jpeg-content');
        });

    return $generator;
}
