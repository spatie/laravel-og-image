<?php

namespace Spatie\OgImage\Components;

use Closure;
use Illuminate\View\Component;
use Spatie\OgImage\Exceptions\InvalidOgImage;
use Spatie\OgImage\OgImage;

class OgImageComponent extends Component
{
    public function __construct(
        public ?string $view = null,
        public array $data = [],
        public ?string $format = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $url = null,
    ) {
        if (($width === null) !== ($height === null)) {
            throw InvalidOgImage::widthAndHeightMustBothBeProvided();
        }
    }

    public function render(): Closure
    {
        return function (array $data) {
            if ($this->url) {
                $url = e($this->url);

                return "<template data-og-image data-og-url=\"{$url}\"></template>";
            }

            $html = $this->view
                ? view($this->view, $this->data)->render()
                : trim($data['slot']->toHtml());

            return app(OgImage::class)->html($html, $this->format, $this->width, $this->height)->toHtml();
        };
    }
}
