<?php

namespace Spatie\OgImage\Support;

class TemplateExtractor
{
    public static function extract(string $html): ?array
    {
        if (! preg_match('/<template\s+data-og-image(?P<attrs>[^>]*)>(?P<content>.*?)<\/template>/s', $html, $matches)) {
            return null;
        }

        return [
            'content' => $matches['content'],
            'hash' => self::extractStringAttribute('data-og-hash', $matches['attrs']),
            'format' => self::extractStringAttribute('data-og-format', $matches['attrs']),
            'width' => self::extractIntAttribute('data-og-width', $matches['attrs']),
            'height' => self::extractIntAttribute('data-og-height', $matches['attrs']),
        ];
    }

    protected static function extractIntAttribute(string $name, string $attributes): ?int
    {
        if (! preg_match("/{$name}=\"(\d+)\"/", $attributes, $match)) {
            return null;
        }

        return (int) $match[1];
    }

    protected static function extractStringAttribute(string $name, string $attributes): ?string
    {
        if (! preg_match("/{$name}=\"([^\"]+)\"/", $attributes, $match)) {
            return null;
        }

        return $match[1];
    }
}
