<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\Games\GameCatalog;

class GameController
{
    public function index()
    {
        $this->syncGameCatalog();
        $games=Game::where('active',true)->orderBy('id')->get();
        $families=config('warqna_games_matrix.families',[]);
        $supported=config('warqna_games_matrix.supported',[]);
        return view('games.index',compact('games','families','supported'));
    }

    public function rules()
    {
        $this->syncGameCatalog();
        return view('games.rules',['games'=>Game::where('active',true)->orderBy('key')->get(),'ruleGuide'=>config('warqna_game_rules',[])]);
    }

    private function syncGameCatalog(): void
    {
        try{
            foreach(GameCatalog::all() as $key=>$g){
                Game::updateOrCreate(['key'=>$key],[
                    'name'=>['ar'=>$g['ar'] ?? $key,'en'=>$g['en'] ?? $key],
                    'min_players'=>$g['min'] ?? 2,
                    'max_players'=>$g['max'] ?? 4,
                    'partnership'=>(bool)($g['partners'] ?? false),
                    'rules'=>['engine'=>$g['engine'] ?? 'generic','family'=>$g['family'] ?? 'training','icon'=>$g['icon'] ?? '🃏','targets'=>$g['targets'] ?? [],'summary'=>$g['summary'] ?? 'لعبة ورق احترافية'],
                    'active'=>true,
                ]);
            }
        }catch(\Throwable $e){}
    }
}
