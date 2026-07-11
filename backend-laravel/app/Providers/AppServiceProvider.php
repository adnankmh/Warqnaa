<?php
namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{App,RateLimiter,Schema,URL};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (app()->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        $this->configureLocale();
        $this->configureRateLimits();
    }

    private function configureLocale(): void
    {
        $allowed = ['ar','en','fr','tr','de','es'];
        $locale = 'ar';
        try {
            if (!app()->runningInConsole() && request()->hasSession()) $locale = session('warqna_locale', 'ar');
        } catch (\Throwable) { $locale = 'ar'; }
        if (!in_array($locale, $allowed, true)) {
            try {
                if (Schema::hasTable('site_settings')) $locale = \App\Models\SiteSetting::getValue('default_locale', 'ar');
            } catch (\Throwable) { $locale = 'ar'; }
        }
        if (!in_array($locale, $allowed, true)) $locale = 'ar';
        App::setLocale($locale);
    }

    private function configureRateLimits(): void
    {
        RateLimiter::for('warqna-auth', fn (Request $request) => [
            Limit::perMinute(10)->by(strtolower((string) $request->input('login', $request->input('email', ''))) . '|' . $request->ip()),
            Limit::perHour(60)->by($request->ip()),
        ]);
        RateLimiter::for('warqna-api', fn (Request $request) => [
            Limit::perMinute(180)->by((string) ($request->user()?->id ?: $request->ip())),
        ]);
        RateLimiter::for('warqna-sensitive', fn (Request $request) => [
            Limit::perMinute(12)->by((string) ($request->user()?->id ?: $request->ip())),
            Limit::perHour(80)->by((string) ($request->user()?->id ?: $request->ip())),
        ]);
        RateLimiter::for('warqna-report', fn (Request $request) => [
            Limit::perMinute(3)->by((string) ($request->user()?->id ?: $request->ip())),
            Limit::perDay(30)->by((string) ($request->user()?->id ?: $request->ip())),
        ]);
    }
}
