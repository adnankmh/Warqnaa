<?php
namespace App\Http\Middleware;

use App\Services\Platform\ProductionConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSupportedAppVersion
{
    public function __construct(private readonly ProductionConfigService $config) {}

    public function handle(Request $request, Closure $next): Response
    {
        $platform = strtolower((string) $request->header('X-Warqna-Platform', ''));
        $build = (int) $request->header('X-Warqna-Build', 0);
        if ($platform !== '' && $build > 0) {
            $release = $this->config->release($platform);
            if ($release && !empty($release['required']) && $build < (int) $release['build_number']) {
                return response()->json([
                    'ok' => false,
                    'code' => 'upgrade_required',
                    'message' => 'يلزم تحديث التطبيق قبل المتابعة.',
                    'release' => $release,
                ], 426);
            }
        }
        return $next($request);
    }
}
