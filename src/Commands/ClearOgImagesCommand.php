<?php

namespace Spatie\OgImage\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearOgImagesCommand extends Command
{
    public $signature = 'og-image:clear';

    public $description = 'Delete all generated OG images from the configured disk';

    public function handle(): int
    {
        $disk = Storage::disk(config('og-image.disk', 'public'));
        $path = config('og-image.path', 'og-images');

        $files = $disk->files($path);

        if (empty($files)) {
            $this->components->info('No OG images found.');

            return self::SUCCESS;
        }

        $disk->delete($files);

        $this->components->info('Deleted '.count($files).' OG image(s).');

        return self::SUCCESS;
    }
}
