<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('profiles','active_profile_cover')) $table->string('active_profile_cover',120)->default('cover_royal_gold');
                if (!Schema::hasColumn('profiles','bot_difficulty')) $table->string('bot_difficulty',20)->default('pro');
                if (!Schema::hasColumn('profiles','ui_preferences')) $table->json('ui_preferences')->nullable();
            });
        }

        if (Schema::hasTable('store_items')) {
            $covers = [
                ['cover_royal_gold','غلاف الذهب الملكي','Royal Gold Cover','#4b2d08','#d39b2a',2500],
                ['cover_midnight','غلاف منتصف الليل','Midnight Cover','#020617','#1d4ed8',3800],
                ['cover_emerald','غلاف زمرد القصر','Palace Emerald Cover','#022c22','#10b981',5200],
                ['cover_crimson','غلاف القرمزي','Crimson Cover','#450a0a','#ef4444',6900],
                ['cover_aurora','غلاف الشفق القطبي','Aurora Cover','#042f2e','#7c3aed',8500],
                ['cover_sapphire','غلاف الياقوت الأزرق','Sapphire Cover','#172554','#3b82f6',11000],
                ['cover_rose','غلاف روز غولد','Rose Gold Cover','#4c0519','#fb7185',12500],
                ['cover_desert','غلاف رمال الصحراء','Desert Sand Cover','#422006','#d97706',14500],
                ['cover_obsidian','غلاف الأوبسيديان','Obsidian Cover','#030712','#374151',18000],
                ['cover_pasha','غلاف قصر الباشا','Pasha Palace Cover','#3f0a0a','#f59e0b',24000],
                ['cover_cosmic','غلاف المجرة','Cosmic Cover','#12033a','#06b6d4',32000],
                ['cover_elite','غلاف النخبة البيضاء','White Elite Cover','#334155','#e2e8f0',40000],
            ];
            foreach ($covers as [$key,$ar,$en,$c1,$c2,$price]) {
                DB::table('store_items')->updateOrInsert(['key'=>$key],[
                    'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),
                    'category'=>'profile_cover','price'=>$price,'duration_days'=>null,
                    'payload'=>json_encode(['cover'=>$key,'color1'=>$c1,'color2'=>$c2,'animated'=>true,'tier'=>$price>=18000?'legendary':($price>=7000?'pro':'beginner')],JSON_UNESCAPED_UNICODE),
                    'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            $themes = [
                ['theme_obsidian','أوبسيديان فاخر','Luxury Obsidian','obsidian','#020307','#6b7280',24000],
                ['theme_rose_gold','روز غولد','Rose Gold','rose_gold','#2a0b16','#fb7185',28000],
                ['theme_desert','رمال الصحراء','Desert Sand','desert','#2c1607','#d97706',26000],
                ['theme_forest','الغابة الملكية','Royal Forest','forest','#021c13','#16a34a',30000],
                ['theme_ice','الكريستال الجليدي','Ice Crystal','ice','#071827','#38bdf8',34000],
            ];
            foreach ($themes as [$key,$ar,$en,$theme,$c1,$c2,$price]) {
                DB::table('store_items')->updateOrInsert(['key'=>$key],[
                    'name'=>json_encode(['ar'=>$ar,'en'=>$en],JSON_UNESCAPED_UNICODE),
                    'category'=>'effect','price'=>$price,'duration_days'=>null,
                    'payload'=>json_encode(['theme'=>$theme,'primary'=>$c1,'accent'=>$c2,'tier'=>'legendary'],JSON_UNESCAPED_UNICODE),
                    'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }

        if (Schema::hasTable('site_settings')) {
            $settings = [
                ['ui_button_height','48','number','designer','ارتفاع الأزرار'],
                ['ui_corner_radius','18','number','designer','استدارة الحواف'],
                ['ui_font_scale','1.0','number','designer','مقياس الخط'],
                ['table_ambient_effects','1','bool','designer','مؤثرات الطاولة الهادئة'],
                ['default_bot_difficulty','pro','string','games','ذكاء البوت الافتراضي'],
                ['daily_reward_tokens','100','number','economy','مكافأة الدخول اليومية'],
                ['rewarded_ad_tokens','50','number','economy','مكافأة الإعلان'],
                ['challenge_reward_max','200','number','economy','أقصى مكافأة تحدٍ'],
            ];
            foreach ($settings as [$key,$value,$type,$group,$label]) {
                DB::table('site_settings')->updateOrInsert(['key'=>$key],[
                    'value'=>$value,'type'=>$type,'group'=>$group,'label'=>$label,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                foreach (['active_profile_cover','bot_difficulty','ui_preferences'] as $column) {
                    if (Schema::hasColumn('profiles',$column)) $table->dropColumn($column);
                }
            });
        }
    }
};
