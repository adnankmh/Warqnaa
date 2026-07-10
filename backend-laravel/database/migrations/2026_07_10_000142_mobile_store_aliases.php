<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('store_items')) {
            return;
        }

        $now = now();
        $items = [
            ['key'=>'vip30','name'=>['ar'=>'باشا 30 يوم','en'=>'VIP 30 Days','de'=>'VIP 30 Tage','tr'=>'30 Gün VIP','fr'=>'VIP 30 jours','es'=>'VIP 30 días'],'category'=>'pasha','price'=>34900,'duration_days'=>30,'payload'=>['days'=>30,'preview_icon'=>'👑','tier'=>'featured']],
            ['key'=>'vip90','name'=>['ar'=>'باشا 90 يوم','en'=>'VIP 90 Days','de'=>'VIP 90 Tage','tr'=>'90 Gün VIP','fr'=>'VIP 90 jours','es'=>'VIP 90 días'],'category'=>'pasha','price'=>79900,'duration_days'=>90,'payload'=>['days'=>90,'preview_icon'=>'👑','tier'=>'legendary']],
            ['key'=>'theme_royal','name'=>['ar'=>'الثيم الملكي','en'=>'Royal Theme','de'=>'Königliches Design','tr'=>'Kraliyet Teması','fr'=>'Thème royal','es'=>'Tema real'],'category'=>'effect','price'=>12500,'duration_days'=>null,'payload'=>['effect'=>'theme_royal','theme'=>'royal','preview_icon'=>'🌌','tier'=>'featured']],
            ['key'=>'theme_emerald','name'=>['ar'=>'الثيم الزمردي','en'=>'Emerald Theme','de'=>'Smaragd-Design','tr'=>'Zümrüt Teması','fr'=>'Thème émeraude','es'=>'Tema esmeralda'],'category'=>'effect','price'=>11800,'duration_days'=>null,'payload'=>['effect'=>'theme_emerald','theme'=>'emerald','preview_icon'=>'💚','tier'=>'featured']],
            ['key'=>'card_marble','name'=>['ar'=>'ظهر ورق رخام أبيض','en'=>'White Marble Card Back','de'=>'Weißer Marmor-Kartenrücken','tr'=>'Beyaz Mermer Kart Arkası','fr'=>'Dos marbre blanc','es'=>'Reverso mármol blanco'],'category'=>'card_back','price'=>8900,'duration_days'=>null,'payload'=>['card_back'=>'marble_white','preview_icon'=>'🂠','tier'=>'featured']],
            ['key'=>'emoji_fun','name'=>['ar'=>'حزمة إيموجي مرحة','en'=>'Fun Emoji Pack','de'=>'Spaß-Emoji-Paket','tr'=>'Eğlenceli Emoji Paketi','fr'=>'Pack emoji amusant','es'=>'Paquete emoji divertido'],'category'=>'emoji_pack','price'=>4200,'duration_days'=>null,'payload'=>['emoji_pack'=>'fun_motion','preview_icon'=>'😂','sound_pack'=>'fun_motion_v1']],
            ['key'=>'boost2','name'=>['ar'=>'مسرّع خبرة ×2','en'=>'XP Booster ×2','de'=>'XP-Booster ×2','tr'=>'XP Hızlandırıcı ×2','fr'=>'Booster XP ×2','es'=>'Potenciador XP ×2'],'category'=>'xp_booster','price'=>6800,'duration_days'=>1,'payload'=>['multiplier'=>2,'valid_days'=>1,'preview_icon'=>'⚡']],
            ['key'=>'name_gold','name'=>['ar'=>'لون اسم ذهبي','en'=>'Golden Name Color','de'=>'Goldene Namensfarbe','tr'=>'Altın İsim Rengi','fr'=>'Nom doré','es'=>'Nombre dorado'],'category'=>'name_color','price'=>3500,'duration_days'=>null,'payload'=>['color'=>'#f5c542','frame'=>'name-frame-gold-aura','preview_icon'=>'✨']],
            ['key'=>'table_emerald','name'=>['ar'=>'طاولة زمردية','en'=>'Emerald Table','de'=>'Smaragdtisch','tr'=>'Zümrüt Masa','fr'=>'Table émeraude','es'=>'Mesa esmeralda'],'category'=>'table','price'=>14900,'duration_days'=>null,'payload'=>['table'=>'emerald_gold_v142','preview_icon'=>'🟢','tier'=>'legendary']],
        ];

        foreach ($items as $item) {
            DB::table('store_items')->updateOrInsert(
                ['key' => $item['key']],
                [
                    'name' => json_encode($item['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'category' => $item['category'],
                    'price' => $item['price'],
                    'duration_days' => $item['duration_days'],
                    'payload' => json_encode(array_merge($item['payload'], ['source'=>'v142_mobile']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_items')) {
            return;
        }
        DB::table('store_items')->whereIn('key', [
            'vip30','vip90','theme_royal','theme_emerald','card_marble','emoji_fun','boost2','name_gold','table_emerald',
        ])->delete();
    }
};
