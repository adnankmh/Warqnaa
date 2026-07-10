<?php
namespace App\Services\WarqnaPro;

use App\Services\Games\GameCatalog;
use App\Services\GameEngine\GameFactory;

class EngineCoverageService
{
    public function rows(): array
    {
        $rows=[];
        foreach(GameCatalog::all() as $key=>$meta){
            try{
                $engine=GameFactory::make($key);
                $players=['user:1','user:2','user:3','user:4','user:5','user:6'];
                $max=max(2,min(6,(int)($meta['max'] ?? 4)));
                $state=$engine->initialState(array_slice($players,0,$max), ['target'=>($meta['targets'][0] ?? 31)]);
                $rows[]=[
                    'key'=>$key,
                    'name'=>$meta['ar'] ?? $key,
                    'family'=>$meta['family'] ?? 'other',
                    'engine'=>class_basename($engine),
                    'ok'=>is_array($state) && isset($state['phase'],$state['turn']),
                    'phase'=>$state['phase'] ?? 'missing',
                    'turn'=>$state['turn'] ?? null,
                    'players'=>count($state['players'] ?? []),
                    'quality'=>$state['engine_quality'] ?? 'engine',
                ];
            }catch(\Throwable $e){
                $rows[]=[
                    'key'=>$key,'name'=>$meta['ar'] ?? $key,'family'=>$meta['family'] ?? 'other',
                    'engine'=>'ERROR','ok'=>false,'phase'=>'error','turn'=>null,'players'=>0,'quality'=>'error','error'=>$e->getMessage()
                ];
            }
        }
        return $rows;
    }

    public function summary(): array
    {
        $rows=$this->rows();
        $ok=count(array_filter($rows, fn($r)=>$r['ok']));
        $deep=count(array_filter($rows, fn($r)=>!str_contains((string)$r['engine'],'UniversalSocialGameRules') && !str_contains((string)$r['engine'],'SimpleTurnRules')));
        return [
            'total'=>count($rows),
            'ok'=>$ok,
            'percent'=>count($rows)?round($ok*100/count($rows)):0,
            'deep_engines'=>$deep,
            'fallback_engines'=>count($rows)-$deep,
            'rows'=>$rows,
        ];
    }
}
