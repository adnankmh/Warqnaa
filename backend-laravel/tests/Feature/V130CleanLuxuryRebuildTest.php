<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class V130CleanLuxuryRebuildTest extends TestCase
{
    public function test_layout_has_one_clean_temporary_games_menu(): void
    {
        $layout=file_get_contents(resource_path('views/layouts/app.blade.php'));
        $this->assertSame(1, substr_count($layout, 'id="gamesCurtain"'));
        $this->assertStringContainsString('wz-games-menu-v130', $layout);
        $this->assertStringContainsString('data-game-link-v130', $layout);
        $this->assertStringNotContainsString('curtain-subtitle', $layout);
    }

    public function test_games_page_uses_v130_wall_not_old_sidebar(): void
    {
        $view=file_get_contents(resource_path('views/games/index.blade.php'));
        $this->assertStringContainsString('wz-lobby-v130', $view);
        $this->assertStringContainsString('wz-games-wall-v130', $view);
        $this->assertStringNotContainsString('wz-lobby-side-v123', $view);
    }

    public function test_every_game_engine_starts_and_has_turn(): void
    {
        foreach(GameCatalog::all() as $key=>$meta){
            $players=['user:1','user:2','user:3','user:4','user:5','user:6'];
            $max=max(2,min(6,(int)($meta['max'] ?? 4)));
            $state=GameFactory::make($key)->initialState(array_slice($players,0,$max), ['target'=>31]);
            $this->assertIsArray($state, $key);
            $this->assertNotEmpty($state['phase'] ?? null, $key);
            $this->assertNotEmpty($state['turn'] ?? null, $key);
        }
    }
}
