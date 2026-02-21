<?php

use Illuminate\Support\Facades\Cache;

it('renders template tag and meta tags from blade component', function () {
    $view = $this->blade('<x-og-image><div>Hello World</div></x-og-image>');

    $view->assertSee('<template data-og-image>', false);
    $view->assertSee('</template>', false);
    $view->assertSee('<meta property="og:image"', false);
    $view->assertSee('<meta name="twitter:image"', false);
    $view->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('includes the slot content inside the template tag', function () {
    $view = $this->blade('<x-og-image><div class="my-og-content">Hello</div></x-og-image>');

    $view->assertSee('<template data-og-image><div class="my-og-content">Hello</div></template>', false);
});

it('stores the page url in cache', function () {
    $slotHtml = '<div>Hello World</div>';

    $this->blade('<x-og-image>'.$slotHtml.'</x-og-image>');

    $hash = md5($slotHtml);

    expect(Cache::get("og-image:{$hash}"))->toBe('http://localhost');
});

it('accepts a format attribute', function () {
    $view = $this->blade('<x-og-image format="webp"><div>Hello</div></x-og-image>');

    $view->assertSee('.webp', false);
});

it('renders a view when view attribute is provided', function () {
    $view = $this->blade('<x-og-image view="og-image.post" :data="[\'title\' => \'My Post\']" />');

    $view->assertSee('<template data-og-image>', false);
    $view->assertSee('My Post', false);
    $view->assertSee('<meta property="og:image"', false);
});

it('stores the cache when using a view', function () {
    $this->blade('<x-og-image view="og-image.post" :data="[\'title\' => \'My Post\']" />');

    $html = view('og-image.post', ['title' => 'My Post'])->render();
    $hash = md5($html);

    expect(Cache::get("og-image:{$hash}"))->not->toBeNull();
});

it('accepts a format attribute with a view', function () {
    $view = $this->blade('<x-og-image view="og-image.post" :data="[\'title\' => \'My Post\']" format="webp" />');

    $view->assertSee('.webp', false);
});

it('accepts width and height attributes', function () {
    $view = $this->blade('<x-og-image :width="800" :height="400"><div>Custom Size</div></x-og-image>');

    $view->assertSee('data-og-width="800"', false);
    $view->assertSee('data-og-height="400"', false);
});

it('uses a different hash when width and height are provided', function () {
    $slotHtml = '<div>Hello World</div>';

    $this->blade('<x-og-image :width="800" :height="400">'.$slotHtml.'</x-og-image>');

    $hashWithDimensions = md5($slotHtml.'-800x400');
    $hashWithout = md5($slotHtml);

    expect(Cache::get("og-image:{$hashWithDimensions}"))->toBe('http://localhost');
    expect(Cache::get("og-image:{$hashWithout}"))->toBeNull();
});

it('stores dimensions in cache when provided', function () {
    $slotHtml = '<div>Hello World</div>';

    $this->blade('<x-og-image :width="800" :height="400">'.$slotHtml.'</x-og-image>');

    $hash = md5($slotHtml.'-800x400');

    expect(Cache::get("og-image-dimensions:{$hash}"))->toBe(['width' => 800, 'height' => 400]);
});

it('does not include dimension attributes without width and height', function () {
    $view = $this->blade('<x-og-image><div>No Size</div></x-og-image>');

    $view->assertDontSee('data-og-width', false);
    $view->assertDontSee('data-og-height', false);
});
