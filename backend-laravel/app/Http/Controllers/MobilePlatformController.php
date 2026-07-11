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
            'version' => config('warqna.version', '1.57.0'),
            'build' => (int) config('warqna.build', 156),
            'time' => now()->toIso8601String(),
        ]);
    }
}
