<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GameEngine\GameFactory;
use App\Services\GameEngine\GlobalCardEngineRules;

class V133GlobalEnginesAdminStoreTest extends TestCase
{
    public function test_uploaded_global_engines_are_used_for_other_games(): void
    {
        foreach(['syrian_tarneeb','tarneeb_400','hand','hand_partner','saudi_hand','banakil','pinochle','solitaire_multiplayer','trix','trix_partner','trix_complex','baloot'] as $key){
            $engine=GameFactory::make($key);
            $this->assertInstanceOf(GlobalCardEngineRules::class,$engine,$key);
            $state=$engine->initialState(['user:1','user:2','user:3','user:4'],[]);
            $this->assertArrayHasKey('_global_engine',$state,$key);
            $this->assertSame('global_card_engine_final_v1',$state['engine_quality'],$key);
            $this->assertNotEmpty($state['turn'],$key);
        }
    }

    public function test_game_categories_are_reduced_to_all_only(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $games=file_get_contents(resource_path('views/games/index.blade.php'));
        $this->assertStringContainsString("\$navFamilies=['all'=>'الكل'];",$layout);
        $this->assertStringContainsString("\$familyLabels=['all'=>'الكل'];",$games);
    }

    public function test_basha_uploaded_asset_and_store_ajax_script_exist(): void
    {
        $this->assertFileExists(public_path('assets/store/basha1.png'));
        $js=file_get_contents(public_path('assets/js/app.js'));
        $css=file_get_contents(public_path('assets/css/app.css'));
        $this->assertStringContainsString('v133 — no-refresh store',$js);
        $this->assertStringContainsString('/assets/store/basha1.png',$css);
    }

    public function test_admin_v133_controls_exist(): void
    {
        $view=file_get_contents(resource_path('views/admin/index.blade.php'));
        $this->assertStringContainsString('admin-players-v133',$view);
        $this->assertStringContainsString('توكنز مخفية',$view);
        $this->assertStringContainsString('إضافة صداقة',$view);
        $this->assertStringContainsString('filterAdminPlayersV133',$view);
    }
}
