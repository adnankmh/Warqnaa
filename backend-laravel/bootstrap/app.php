<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );
        // Apple Sign in uses response_mode=form_post. The callback is protected
        // by the one-time OAuth state and must not be rejected by Laravel CSRF.
        $middleware->validateCsrfTokens(except: ['auth/social/*/callback']);
        $middleware->append(\App\Http\Middleware\RequestContext::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'supported.app' => \App\Http\Middleware\RequireSupportedAppVersion::class,
            'maintenance.guard' => \App\Http\Middleware\RejectMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            $status = match (true) {
                $e instanceof \Illuminate\Validation\ValidationException => 422,
                $e instanceof \Illuminate\Auth\AuthenticationException => 401,
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };
            $message = $e->getMessage() ?: 'لا يمكن تنفيذ هذه الخطوة الآن.';
            $errors = null;
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $message = 'تحقق من البيانات المدخلة.';
                $errors = $e->errors();
            }
            if ($status >= 500) {
                report($e);
                $message = app()->environment('local')
                    ? 'خطأ تقني: '.mb_substr($e->getMessage(), 0, 220)
                    : 'حدث خطأ غير متوقع. حاول لاحقًا أو أرسل رقم الطلب إلى الدعم.';
            }
            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'errors' => $errors,
                    'request_id' => $request->attributes->get('request_id'),
                ], $status);
            }
            if ($status >= 500) return back()->withErrors(['msg' => $message]);
            return null;
        });
    })->create();
