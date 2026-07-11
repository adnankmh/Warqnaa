<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('games')) {
            return;
        }

        $active = [
            'tarneeb', 'trix', 'hand', 'banakil', 'baloot', 'basra',
            'tarneeb_400', 'syrian_tarneeb', 'trix_complex', 'saudi_hand',
            'hand_partner', 'trix_partner',
        ];

        DB::table('games')->update(['active' => false, 'updated_at' => now()]);
        DB::table('games')->whereIn('key', $active)->update(['active' => true, 'updated_at' => now()]);
        DB::table('games')->where('key', 'banakil')->update([
            'name' => json_encode(['ar' => 'بناكل', 'en' => 'Banakil'], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
        DB::table('games')->where('key', 'basra')->update([
            'name' => json_encode(['ar' => 'باصرة', 'en' => 'Basra'], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('store_items')) {
            $aliases = [
                ['theme_dark_premium','داكن فاخر','Premium Dark','effect',0,['theme'=>'dark']],
                ['theme_classic','كلاسيكي فاخر','Luxury Classic','effect',9800,['theme'=>'classic']],
                ['theme_purple','بنفسجي أسطوري','Legendary Purple','effect',14500,['theme'=>'purple']],
                ['name_red','أحمر لامع','Bright Red','name_color',6500,['color'=>'#ef4444']],
                ['name_blue','أزرق ملكي','Royal Blue','name_color',6500,['color'=>'#3b82f6']],
                ['name_green','أخضر زمردي','Emerald Green','name_color',6500,['color'=>'#22c55e']],
                ['name_purple','بنفسجي','Purple','name_color',6500,['color'=>'#a855f7']],
                ['name_cyan','سماوي','Cyan','name_color',6500,['color'=>'#06b6d4']],
                ['name_white','أبيض ألماسي','Diamond White','name_color',6500,['color'=>'#ffffff']],
                ['badge_king','شارة الملك','King Badge','badge',30000,['badge'=>'king','icon'=>'👑']],
                ['badge_pro','شارة المحترف','Pro Badge','badge',30000,['badge'=>'pro','icon'=>'🔥']],
                ['effect_gold_entry','دخول ذهبي','Golden Entry','effect',42000,['effect'=>'gold_entry','icon'=>'✨']],
                ['effect_fire_win','احتفال فوز ناري','Fire Win Celebration','effect',48000,['effect'=>'fire_win','icon'=>'🔥']],
                ['effect_royal_confetti','قصاصات ملكية','Royal Confetti','effect',55000,['effect'=>'royal_confetti','icon'=>'🎉']],
                ['emoji_huge_reactions','ردود فعل عملاقة','Huge Reactions','emoji_pack',50000,['emojis'=>'😂 👑 🔥 😡 😭 🤯','animated'=>true,'large'=>true]],
            ];
            foreach ($aliases as [$key,$ar,$en,$category,$price,$payload]) {
                DB::table('store_items')->updateOrInsert(['key'=>$key],[
                    'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),
                    'category'=>$category,'price'=>$price,'duration_days'=>null,
                    'payload'=>json_encode($payload,JSON_UNESCAPED_UNICODE),
                    'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Catalog curation is intentionally not reversed.
    }
};
