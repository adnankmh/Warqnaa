<?php
namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $allowed=['ar','en','fr','tr','de','es'];
        $locale='ar';
        try{
            if(!app()->runningInConsole() && request()->hasSession()) $locale=session('warqna_locale','ar');
        }catch(\Throwable $e){ $locale='ar'; }
        if(!in_array($locale,$allowed,true)){
            try{
                if(Schema::hasTable('site_settings')){
                    $locale=\App\Models\SiteSetting::getValue('default_locale','ar');
                }
            }catch(\Throwable $e){
                $locale='ar';
            }
        }
        if(!in_array($locale,$allowed,true)) $locale='ar';
        App::setLocale($locale);
    }
}
