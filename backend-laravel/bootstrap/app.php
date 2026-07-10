<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php', health: '/up')
 ->withMiddleware(function (Middleware $middleware) {
  $middleware->alias(['admin' => \App\Http\Middleware\AdminOnly::class]);
 })
 ->withExceptions(function (Exceptions $exceptions) {
  $exceptions->render(function (Throwable $e, Request $request) {
   $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
   $msg = $e->getMessage() ?: 'لا يمكن تنفيذ هذه الخطوة الآن.';
   if($status >= 500) $msg = app()->environment('local') ? ('خطأ تقني: '.mb_substr($e->getMessage(),0,220)) : 'حدث خطأ غير متوقع، لم يتوقف الموقع. أعد المحاولة أو راجع الإدارة.';
   if($request->expectsJson() || $request->ajax()) return response()->json(['ok'=>false,'message'=>$msg], $status >= 500 ? 200 : $status);
   if($status !== 404) return back()->withErrors(['msg'=>$msg]);
   return null;
  });
 })->create();
