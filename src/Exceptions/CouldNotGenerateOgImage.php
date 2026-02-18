<?php

namespace Spatie\OgImage\Exceptions;

use Exception;

class CouldNotGenerateOgImage extends Exception
{
    public static function htmlNotFound(string $hash): self
    {
        return new self("Could not find the HTML for OG image with hash [{$hash}]. The cache entry may have expired.");
    }
}
