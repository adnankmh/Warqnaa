<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = trim((string) $request->header('X-Request-ID')) ?: (string) Str::uuid();
        $request->attributes->set('request_id', mb_substr($requestId, 0, 64));
        $response = $next($request);
        $response->headers->set('X-Request-ID', $request->attributes->get('request_id'));
        $response->headers->set('X-Warqna-Version', (string) config('warqna.version', '1.54.0'));
        return $response;
    }
}
