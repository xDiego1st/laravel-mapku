<?php

namespace Laragiser\Mapping;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laragiser\Mapping\Commands\GenerateDocumentationPdfCommand;
use Laragiser\Mapping\Commands\PublishAssetsCommand;
use Laragiser\Mapping\Services\BoundaryService;
use Laragiser\Mapping\Services\MappingManager;
use Laragiser\Mapping\Services\TileProxyService;
use Laragiser\Mapping\View\Components\DynamicMap;
use Laragiser\Mapping\View\Components\Map;
use Livewire\Livewire;

class MappingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mapping.php',
            'mapping'
        );

        $this->app->singleton(TileProxyService::class, function () {
            return new TileProxyService();
        });

        $this->app->singleton(BoundaryService::class, function () {
            return new BoundaryService();
        });

        $this->app->singleton(MappingManager::class, function ($app) {
            return new MappingManager(
                $app->make(TileProxyService::class),
                $app->make(BoundaryService::class)
            );
        });

        $this->app->alias(MappingManager::class, 'mapping');
    }

    public function boot(): void
    {
        // 1. Load Views & Routes
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mapping');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // 2. Publishable Resources
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../config/mapping.php' => config_path('mapping.php'),
            ], 'mapping-config');

            // Assets (JS, CSS, & Fonts)
            $this->publishes([
                __DIR__ . '/../resources/js' => public_path('vendor/laravel-mapping/js'),
                __DIR__ . '/../resources/css' => public_path('vendor/laravel-mapping/css'),
                __DIR__ . '/../resources/fonts' => public_path('vendor/laravel-mapping/fonts'),
            ], 'mapping-assets');

            // Default GeoJSON (Pekanbaru)
            $this->publishes([
                __DIR__ . '/../resources/geojson' => resource_path('geojson'),
            ], 'mapping-geojson');

            // Views (Opsional untuk custom blade)
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/mapping'),
            ], 'mapping-views');

            // Commands
            $this->commands([
                PublishAssetsCommand::class,
                GenerateDocumentationPdfCommand::class,
            ]);
        }

        // 3. Register Blade Components
        Blade::component('mapping-map', Map::class);
        Blade::component('mapping::map', Map::class);

        // 4. Register Livewire Component (if Livewire exists)
        if (class_exists(Livewire::class)) {
            Livewire::component('mapping-canvas', DynamicMap::class);
        }

        // 5. Register Blade Directives: @mappingStyles & @mappingScripts
        Blade::directive('mappingStyles', function () {
            return '<?php 
                $cssPath = public_path("vendor/laravel-mapping/css/mapping.css");
                $v = file_exists($cssPath) ? filemtime($cssPath) : time();
                echo \'<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css"><link rel="stylesheet" href="\' . asset("vendor/laravel-mapping/css/mapping.css") . \'?v=\' . $v . \'">\'; 
            ?>';
        });

        Blade::directive('mappingScripts', function () {
            return '<?php 
                $jsPath = public_path("vendor/laravel-mapping/js/laravel-mapping.js");
                $v = file_exists($jsPath) ? filemtime($jsPath) : time();
                echo \'<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script><script src="\' . asset("vendor/laravel-mapping/js/laravel-mapping.js") . \'?v=\' . $v . \'"></script>\'; 
            ?>';
        });
    }
}
