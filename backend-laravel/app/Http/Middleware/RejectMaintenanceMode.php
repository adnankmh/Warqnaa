<?php
namespace App\Http\Middleware;

use App\Services\Platform\ProductionConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectMaintenanceMode
{
    public function __construct(private readonly ProductionConfigService $config) {}

    public function handle(Request $request, Closure $next): Response
    {
        $flags = $this->config->flags();
        $maintenance = (bool) data_get($flags, 'maintenance_mode.enabled', false);
        if ($maintenance && !$request->user()?->is_admin) {
            return response()->json([
                'ok' => false,
                'code' => 'maintenance',
                'message' => (string) data_get($flags, 'maintenance_mode.payload.message', 'المنصة تحت الصيانة المؤقتة.'),
            ], 503);
        }
        return $next($request);
    }
}
