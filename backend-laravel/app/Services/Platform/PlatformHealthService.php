<?php
namespace App\Services\Platform;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class PlatformHealthService
{
    public function snapshot(): array
    {
        $tables=['users','games','rooms','messages','notifications','store_items','inventory_items','friendships','presence_sessions','economy_seasons','store_offers','rare_collectibles'];
        $checks=[];
        foreach($tables as $t) $checks[$t]=Schema::hasTable($t);
        return [
            'ok'=>!in_array(false,$checks,true),
            'version'=>config('warqna_pro_features.version','v117'),
            'realtime_mode'=>config('warqna_realtime.mode','polling'),
            'database'=>$checks,
            'counts'=>$this->counts(),
            'time'=>now()->toIso8601String(),
        ];
    }

    private function counts(): array
    {
        $map=['users'=>'users','games'=>'games','rooms'=>'rooms','messages'=>'messages','notifications'=>'notifications','store_items'=>'store_items'];
        $out=[];
        foreach($map as $key=>$table){
            try{$out[$key]=Schema::hasTable($table)?DB::table($table)->count():0;}catch(\Throwable $e){$out[$key]=0;}
        }
        return $out;
    }
}
