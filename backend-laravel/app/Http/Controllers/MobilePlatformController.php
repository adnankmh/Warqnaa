<?php
namespace App\Http\Controllers;

use App\Services\Platform\ProductionConfigService;
use Illuminate\Http\Request;

class MobilePlatformController extends Controller
{
    public function config(Request $request, ProductionConfigService $config)
    {
        $platform = strtolower((string) $request->query('platform', $request->header('X-Warqna-Platform', 'web')));
        if (!in_array($platform, ['web','android','ios'], true)) $platform = 'web';
        return response()->json(['ok' => true, 'config' => $config->publicConfig($platform)]);
    }

    public function health()
    {
        return response()->json([
            'ok' => true,
            'service' => 'warqna-api',
            'version' => config('warqna.version', '1.59.0'),
            'build' => (int) config('warqna.build', 159),
            'time' => now()->toIso8601String(),
        ]);
    }

    public function legacyHealth()
    {
        return response()->json([
            'ok' => true,
            'version' => config('warqna.version', '1.59.0'),
            'pwa' => true,
            'icons' => true,
            'offline' => true,
            'time' => now()->toIso8601String(),
        ]);
    }

    public function legacyBootstrap()
    {
        return response()->json([
            'ok' => true,
            'app' => config('app.name', 'Warqna Zone'),
            'version' => config('warqna.version', '1.59.0'),
            'apk_ready' => (bool) config('warqna_mobile.apk_ready', true),
            'mobile' => config('warqna_mobile.features', []),
        ]);
    }

    public function legacyGames()
    {
        return response()->json([
            'ok' => true,
            'games' => \App\Services\Games\GameCatalog::all(),
        ]);
    }
}
