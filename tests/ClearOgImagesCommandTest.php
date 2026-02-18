<?php

use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('can clear generated og images', function () {
    Storage::disk('public')->put('og-images/abc123.png', 'content');
    Storage::disk('public')->put('og-images/def456.png', 'content');

    $this->artisan('og-image:clear')
        ->expectsOutputToContain('Deleted 2 OG image(s)')
        ->assertSuccessful();

    expect(Storage::disk('public')->files('og-images'))->toBeEmpty();
});

it('shows message when no images exist', function () {
    $this->artisan('og-image:clear')
        ->expectsOutputToContain('No OG images found')
        ->assertSuccessful();
});
