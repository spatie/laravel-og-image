<?php

namespace Spatie\OgImage;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\OgImage\Commands\ClearOgImagesCommand;
use Spatie\OgImage\Components\OgImageComponent;
use Spatie\OgImage\Http\Controllers\OgImageController;

class OgImageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('og-image')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(ClearOgImagesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OgImage::class);
        $this->app->singleton(OgImageGenerator::class);
    }

    public function packageBooted(): void
    {
        Blade::component('og-image', OgImageComponent::class);

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        $prefix = config('og-image.route_prefix', 'og-image');
        $middleware = config('og-image.route_middleware', ['web']);

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group(function () {
                Route::get('{filename}', OgImageController::class)
                    ->where('filename', '[a-f0-9]+\.(png|jpeg|jpg|webp)')
                    ->name('og-image.serve');
            });
    }
}
