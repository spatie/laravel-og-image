<?php

it('can resolve the og image service', function () {
    expect(app(\Spatie\OgImage\OgImage::class))->toBeInstanceOf(\Spatie\OgImage\OgImage::class);
});

it('can resolve the og image generator', function () {
    expect(app(\Spatie\OgImage\OgImageGenerator::class))->toBeInstanceOf(\Spatie\OgImage\OgImageGenerator::class);
});
