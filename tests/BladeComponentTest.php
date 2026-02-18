<?php

use Illuminate\Support\Facades\Cache;

it('renders meta tags from blade component', function () {
    $view = $this->blade('<x-og-image><div>Hello World</div></x-og-image>');

    $view->assertSee('<meta property="og:image"', false);
    $view->assertSee('<meta name="twitter:image"', false);
    $view->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('stores the slot html in cache', function () {
    $slotHtml = '<div>Hello World</div>';

    $this->blade('<x-og-image>'.$slotHtml.'</x-og-image>');

    $hash = md5($slotHtml);

    expect(Cache::get("og-image:{$hash}"))->toBe($slotHtml);
});

it('accepts a format attribute', function () {
    $view = $this->blade('<x-og-image format="webp"><div>Hello</div></x-og-image>');

    $view->assertSee('.webp', false);
});

it('does not render the slot content visually', function () {
    $view = $this->blade('<x-og-image><div class="my-og-content">Secret</div></x-og-image>');

    $view->assertDontSee('my-og-content');
});
