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

    $generator = new FakeOgImageGenerator;
    $generator->generateForUrl($pageUrl);

    expect($generator->generatedUrl)->toContain('https://example.com/page?ogimage');
});

it('appends the preview parameter to a url with existing query parameters', function () {
    $pageUrl = 'https://example.com/page?foo=bar';

    fakePageWithOgTemplate($pageUrl);

    $generator = new FakeOgImageGenerator;
    $generator->generateForUrl($pageUrl);

    expect($generator->generatedUrl)
        ->toContain('?foo=bar&ogimage')
        ->not->toContain('?foo=bar?ogimage');
});

function fakePageWithOgTemplate(string $pageUrl): void
{
    $ogHtml = app(OgImage::class)->html('<div>Hello OG</div>');

    Http::fake([
        $pageUrl => Http::response("<html><head></head><body>{$ogHtml}</body></html>"),
    ]);
}

class FakeOgImageGenerator extends OgImageGenerator
{
    public ?string $generatedUrl = null;

    public function generate(string $url, string $path, ?int $width = null, ?int $height = null): void
    {
        $this->generatedUrl = $url;

        Storage::disk('public')->put($path, 'fake-jpeg-content');
    }
}
