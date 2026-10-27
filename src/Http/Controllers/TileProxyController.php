<?php

namespace Laragiser\Mapping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Laragiser\Mapping\Services\TileProxyService;

class TileProxyController extends Controller
{
    public function __construct(
        protected TileProxyService $tileProxyService
    ) {}

    /**
     * Proxy tile gambar peta.
     *
     * GET /api/mapping/tile/{layer}/{z}/{x}/{y}
     */
    public function tile(Request $request, string $layer, int $z, int $x, int $y): Response
    {
        $tileData = $this->tileProxyService->getTile($layer, $z, $x, $y);

        if (!$tileData) {
            $tileData = $this->tileProxyService->getEmptyTile();
        }

        $body = base64_decode($tileData['body']);
        $etag = '"' . md5($body) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, [
                'Access-Control-Allow-Origin' => '*',
                'ETag' => $etag,
            ]);
        }

        return response($body, 200, [
            'Content-Type' => $tileData['content_type'],
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, If-None-Match',
        ]);
    }

    /**
     * CORS Preflight.
     */
    public function preflight(): Response
    {
        return response('', 204, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, If-None-Match',
            'Access-Control-Max-Age' => '86400',
        ]);
    }
}
