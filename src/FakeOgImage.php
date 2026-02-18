<?php

namespace Spatie\OgImage;

use Closure;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Assert;

class FakeOgImage extends OgImage
{
    /** @var array<int, array{view: string, data: array<string, mixed>}> */
    protected array $renderedViews = [];

    /** @var array<int, string> */
    protected array $renderedHtml = [];

    public function view(string $view, array $data = [], ?string $format = null): HtmlString
    {
        $this->renderedViews[] = ['view' => $view, 'data' => $data];

        $format ??= config('og-image.format', 'png');
        $hash = $this->hash($view.json_encode($data));

        return $this->metaTags($hash, $format);
    }

    public function html(string $html, ?string $format = null): HtmlString
    {
        $this->renderedHtml[] = $html;

        $format ??= config('og-image.format', 'png');
        $hash = $this->hash($html);

        return $this->metaTags($hash, $format);
    }

    public function assertViewRendered(string|Closure|null $viewOrCallback = null): void
    {
        Assert::assertNotEmpty($this->renderedViews, 'No OG image views were rendered.');

        if ($viewOrCallback === null) {
            return;
        }

        if ($viewOrCallback instanceof Closure) {
            $found = collect($this->renderedViews)->contains(
                fn (array $rendered) => $viewOrCallback($rendered['view'], $rendered['data']),
            );

            Assert::assertTrue($found, 'No rendered OG image view matched the given callback.');

            return;
        }

        $views = array_column($this->renderedViews, 'view');

        Assert::assertContains($viewOrCallback, $views, "The OG image view [{$viewOrCallback}] was not rendered.");
    }

    public function assertHtmlRendered(string|Closure|null $htmlOrCallback = null): void
    {
        Assert::assertNotEmpty($this->renderedHtml, 'No OG image HTML was rendered.');

        if ($htmlOrCallback === null) {
            return;
        }

        if ($htmlOrCallback instanceof Closure) {
            $found = collect($this->renderedHtml)->contains(
                fn (string $html) => $htmlOrCallback($html),
            );

            Assert::assertTrue($found, 'No rendered OG image HTML matched the given callback.');

            return;
        }

        Assert::assertContains($htmlOrCallback, $this->renderedHtml, 'The given HTML was not rendered as an OG image.');
    }

    public function assertNothingRendered(): void
    {
        Assert::assertEmpty($this->renderedViews, 'OG image views were rendered unexpectedly.');
        Assert::assertEmpty($this->renderedHtml, 'OG image HTML was rendered unexpectedly.');
    }
}
