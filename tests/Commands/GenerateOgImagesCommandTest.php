<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('shows failure when url has no template tag', function () {
    Http::fake([
        'https://example.com/page' => Http::response('<html><body>No template</body></html>'),
    ]);

    $this->artisan('og-image:generate', ['urls' => ['https://example.com/page']])
        ->expectsOutputToContain('Failed')
        ->assertFailed();
});
