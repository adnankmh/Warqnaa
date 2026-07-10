<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('profiles','avatar_data')) $table->longText('avatar_data')->nullable();
                if (!Schema::hasColumn('profiles','login_streak')) $table->unsignedInteger('login_streak')->default(0);
                if (!Schema::hasColumn('profiles','last_login_reward_at')) $table->timestamp('last_login_reward_at')->nullable();
                if (!Schema::hasColumn('profiles','name_color_expires_at')) $table->timestamp('name_color_expires_at')->nullable();
                if (!Schema::hasColumn('profiles','chat_color_expires_at')) $table->timestamp('chat_color_expires_at')->nullable();
            });
        }
        if (!Schema::hasTable('rewarded_ad_claims')) {
            Schema::create('rewarded_ad_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('claim_date');
                $table->unsignedInteger('reward_tokens')->default(0);
                $table->unsignedInteger('reward_xp')->default(0);
                $table->string('network')->default('admob');
                $table->string('verification_id')->unique();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->index(['user_id','claim_date']);
            });
        }

        if (Schema::hasTable('store_items')) {
            $palette = [
                ['ruby','#ff355e','ياقوت متوهج','Ruby Glow'],['orange','#ff8a00','برتقالي شمسي','Solar Orange'],
                ['amber','#ffc107','كهرماني فاخر','Luxury Amber'],['gold','#f5c542','ذهبي ملكي','Royal Gold'],
                ['lime','#a3e635','ليموني نيون','Neon Lime'],['emerald','#10b981','زمرد عميق','Deep Emerald'],
                ['mint','#5eead4','نعناع نيون','Neon Mint'],['cyan','#22d3ee','سماوي ليزر','Laser Cyan'],
                ['sky','#38bdf8','أزرق سماوي','Sky Blue'],['royal','#3b82f6','أزرق ملكي','Royal Blue'],
                ['indigo','#6366f1','نيلي فاخر','Luxury Indigo'],['violet','#8b5cf6','بنفسجي مخملي','Velvet Violet'],
                ['amethyst','#c084fc','جمشت لامع','Shiny Amethyst'],['pink','#ec4899','وردي لؤلؤي','Pearl Pink'],
                ['rose','#fb7185','وردي غروب','Sunset Rose'],['coral','#fb7185','مرجاني فاخر','Luxury Coral'],
                ['silver','#cbd5e1','فضي معدني','Metallic Silver'],['diamond','#e0f2fe','ألماسي جليدي','Ice Diamond'],
                ['white','#ffffff','أبيض ناصع','Pure White'],['bronze','#c08457','برونزي قديم','Antique Bronze'],
                ['teal','#14b8a6','تركوازي محترف','Pro Teal'],['magenta','#d946ef','ماجنتا أسطوري','Legendary Magenta'],
                ['crimson','#dc2626','قرمزي ناري','Fire Crimson'],['aurora','#67e8f9','شفق قطبي','Aurora'],
            ];
            $durations = [3,7,30];
            $themes = [
                ['theme_crimson_legend','قرمزي أسطوري','Legendary Crimson','#21070d','#ef334f','crimson',34000],
                ['theme_midnight_elite','منتصف الليل النخبوي','Elite Midnight','#020617','#4f86ff','midnight',42000],
                ['theme_aurora_supreme','الشفق الفاخر','Supreme Aurora','#04171b','#67e8f9','aurora',52000],
            ];
            foreach ($themes as [$key,$ar,$en,$primary,$accent,$theme,$price]) {
                DB::table('store_items')->updateOrInsert(['key'=>$key],[
                    'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),
                    'category'=>'effect','price'=>$price,'duration_days'=>null,
                    'payload'=>json_encode(['theme'=>$theme,'primary'=>$primary,'accent'=>$accent,'tier'=>'legendary'],JSON_UNESCAPED_UNICODE),
                    'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }

            foreach ($palette as $index => [$key,$color,$ar,$en]) {
                $days = $durations[$index % 3];
                $tier = $days === 3 ? 'beginner' : ($days === 7 ? 'pro' : 'legendary');
                $base = $days === 3 ? 2400 : ($days === 7 ? 6200 : 16800);
                DB::table('store_items')->updateOrInsert(['key'=>"player_color_{$key}_{$days}d"],[
                    'name'=>json_encode(['ar'=>"لون لاعب {$ar} — {$days} أيام",'en'=>"{$en} player color — {$days} days"],JSON_UNESCAPED_UNICODE),
                    'category'=>'name_color','price'=>$base + ($index*175),'duration_days'=>$days,
                    'payload'=>json_encode(['color'=>$color,'glow'=>true,'tier'=>$tier],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
                DB::table('store_items')->updateOrInsert(['key'=>"chat_color_{$key}_{$days}d"],[
                    'name'=>json_encode(['ar'=>"لون دردشة {$ar} — {$days} أيام",'en'=>"{$en} chat color — {$days} days"],JSON_UNESCAPED_UNICODE),
                    'category'=>'text_color','price'=>$base + 900 + ($index*175),'duration_days'=>$days,
                    'payload'=>json_encode(['color'=>$color,'glow'=>true,'tier'=>$tier],JSON_UNESCAPED_UNICODE),'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rewarded_ad_claims');
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                foreach (['avatar_data','login_streak','last_login_reward_at','name_color_expires_at','chat_color_expires_at'] as $column) {
                    if (Schema::hasColumn('profiles',$column)) $table->dropColumn($column);
                }
            });
        }
    }
};
