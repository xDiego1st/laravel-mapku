<?php

namespace Laragiser\Mapping\Facades;

use Illuminate\Support\Facades\Facade;
use Laragiser\Mapping\Services\MappingManager;

/**
 * @method static array getDefaultCenter()
 * @method static int getDefaultZoom()
 * @method static array getTileLayers()
 * @method static array getBoundaryConfig()
 * @method static \Laragiser\Mapping\Services\TileProxyService tileProxy()
 * @method static \Laragiser\Mapping\Services\BoundaryService boundary()
 *
 * @see \Laragiser\Mapping\Services\MappingManager
 */
class Mapping extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MappingManager::class;
    }
}
