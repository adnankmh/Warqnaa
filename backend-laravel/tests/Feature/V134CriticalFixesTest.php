<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GameEngine\GameFactory;
use App\Services\WarqnaPro\StoreCatalogService;

class V134CriticalFixesTest extends TestCase
{
    public function test_admin_view_routes_are_defined(): void
    {
        $view=file_get_contents(resource_path('views/admin/index.blade.php'));
        preg_match_all("/route\('([^']+)'/", $view, $m);
        foreach(array_unique($m[1]) as $name){
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), $name.' route is missing');
        }
    }

    public function test_all_curated_game_engines_start(): void
    {
        foreach(['tarneeb','tarneeb_41','tarneeb_61','syrian_tarneeb','tarneeb_400','hand','hand_partner','saudi_hand','pinochle','banakil','solitaire_multiplayer','trix','trix_partner','trix_complex','baloot'] as $key){
            $state=GameFactory::make($key)->initialState(['user:1','user:2','user:3','user:4'],['target'=>41]);
            $this->assertNotEmpty($state['turn'],$key);
            $this->assertNotEmpty($state['phase'],$key);
        }
    }

    public function test_store_tables_are_curated_50_and_pasha_image_exists(): void
    {
        $store=new StoreCatalogService();
        $this->assertCount(50,$store->tableSkins());
        $this->assertFileExists(public_path('assets/store/basha1.png'));
    }

    public function test_settings_country_dropdown_and_private_room_password_exist(): void
    {
        $settings=file_get_contents(resource_path('views/pages/settings.blade.php'));
        $room=file_get_contents(resource_path('views/room/index.blade.php'));
        $this->assertStringContainsString('country-select-v134',$settings);
        $this->assertStringContainsString('privatePasswordInput',$room);
        $this->assertFileExists(config_path('countries.php'));
    }
}
