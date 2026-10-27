<?php

namespace Laragiser\Mapping\Commands;

use Illuminate\Console\Command;

class PublishAssetsCommand extends Command
{
    protected $signature = 'mapping:publish {--force : Timpa file yang sudah ada}';
    protected $description = 'Publikasikan file konfigurasi, asset JavaScript/CSS, dan GeoJSON default package Laravel Mapping';

    public function handle(): int
    {
        $this->info('Mempublikasikan asset Laravel Mapping...');

        $params = ['--tag' => 'mapping-config'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }
        $this->call('vendor:publish', $params);

        $params['--tag'] = 'mapping-assets';
        $this->call('vendor:publish', $params);

        $params['--tag'] = 'mapping-geojson';
        $this->call('vendor:publish', $params);

        $this->info('Asset dan konfigurasi laragiser/laravel-mapping berhasil dipublikasikan!');
        return self::SUCCESS;
    }
}
