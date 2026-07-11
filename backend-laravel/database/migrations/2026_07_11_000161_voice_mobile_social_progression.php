<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('profiles', 'round_points')) $table->unsignedBigInteger('round_points')->default(0)->after('xp');
                if (!Schema::hasColumn('profiles', 'tournament_points')) $table->unsignedBigInteger('tournament_points')->default(0)->after('round_points');
                if (!Schema::hasColumn('profiles', 'club_points')) $table->unsignedBigInteger('club_points')->default(0)->after('tournament_points');
                if (!Schema::hasColumn('profiles', 'xp_boost_expires_at')) $table->timestamp('xp_boost_expires_at')->nullable()->after('xp_boost_multiplier');
            });
        }

        if (!Schema::hasTable('progression_events')) {
            Schema::create('progression_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event_key', 191)->unique();
                $table->string('event_type', 40);
                $table->string('mode', 30)->default('normal');
                $table->unsignedInteger('base_points')->default(0);
                $table->decimal('multiplier', 8, 2)->default(1);
                $table->unsignedInteger('awarded_xp')->default(0);
                $table->unsignedInteger('round_points')->default(0);
                $table->unsignedInteger('tournament_points')->default(0);
                $table->unsignedInteger('club_points')->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->index(['user_id','created_at']);
            });
        }

        if (Schema::hasTable('clubs') && !Schema::hasTable('club_announcements')) {
            Schema::create('club_announcements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('club_id')->constrained()->cascadeOnDelete();
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 140);
                $table->text('body');
                $table->boolean('pinned')->default(false);
                $table->timestamps();
                $table->index(['club_id','pinned','created_at']);
            });
        }

        if (Schema::hasTable('tournaments')) {
            $addClubId = !Schema::hasColumn('tournaments','club_id');
            $addRewardMultiplier = !Schema::hasColumn('tournaments','reward_multiplier');
            $addSponsored = !Schema::hasColumn('tournaments','sponsored');
            if ($addClubId || $addRewardMultiplier || $addSponsored) {
                Schema::table('tournaments', function (Blueprint $table) use ($addClubId,$addRewardMultiplier,$addSponsored) {
                    if ($addClubId) $table->foreignId('club_id')->nullable()->after('creator_id')->constrained()->nullOnDelete();
                    if ($addRewardMultiplier) $table->decimal('reward_multiplier', 8, 2)->default(2);
                    if ($addSponsored) $table->boolean('sponsored')->default(false);
                });
            }
        }

        if (!Schema::hasTable('social_accounts')) {
            Schema::create('social_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('provider', 24);
                $table->string('provider_user_id', 191);
                $table->string('email')->nullable();
                $table->string('display_name')->nullable();
                $table->string('avatar_url', 2048)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->unique(['provider','provider_user_id']);
                $table->unique(['user_id','provider']);
            });
        }

        if (!Schema::hasTable('social_auth_sessions')) {
            Schema::create('social_auth_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('state')->unique();
                $table->string('provider',24);
                $table->string('status',24)->default('pending');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('one_time_token')->nullable();
                $table->text('error')->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && Schema::hasTable('profiles')) {
            $adminIds = DB::table('users')->where('is_admin', true)->pluck('id');
            if ($adminIds->isNotEmpty()) {
                DB::table('profiles')->whereIn('user_id', $adminIds)->update([
                    'pasha_days' => DB::raw('CASE WHEN pasha_days < 3650 THEN 3650 ELSE pasha_days END'),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('store_items')) {
            $items = [
                ['effect_fireworks_royal','ألعاب نارية ملكية','Royal Fireworks','effect',18000,'🎆','legendary'],
                ['effect_lightning_crown','تاج البرق','Lightning Crown','effect',24000,'⚡','legendary'],
                ['effect_golden_confetti','كونفيتي ذهبي','Golden Confetti','effect',12000,'🎊','pro'],
                ['effect_dragon_victory','انتصار التنين','Dragon Victory','effect',45000,'🐉','mythic'],
                ['effect_phoenix_victory','عنقاء النصر','Phoenix Victory','effect',52000,'🔥','mythic'],
                ['effect_crystal_burst','انفجار الكريستال','Crystal Burst','effect',30000,'💎','legendary'],
                ['badge_tournament_champion','بطل المسابقات','Tournament Champion','badge',28000,'🏆','legendary'],
                ['badge_club_commander','قائد النادي','Club Commander','badge',22000,'🛡️','pro'],
                ['badge_voice_host','مضيف صوتي','Voice Host','badge',9000,'🎙️','advanced'],
                ['badge_1000_wins','ألف انتصار','1000 Wins','badge',75000,'👑','mythic'],
                ['emoji_pack_giant_fun','إيموجي المرح العملاقة','Giant Fun Emojis','emoji_pack',5000,'😂🤣😹😆🥳','big'],
                ['emoji_pack_reactions_pro','ردود المحترفين','Pro Reactions','emoji_pack',10000,'🔥👏💪😎🤝','pro'],
                ['emoji_pack_legendary','الإيموجي الأسطورية','Legendary Emojis','emoji_pack',15000,'🐉👑⚡💎🏆','legendary'],
                ['emoji_pack_emotions','المشاعر الكاملة','Full Emotions','emoji_pack',3500,'😡😭🥺😍😱','emotions'],
                ['emoji_pack_sports','الحماس الرياضي','Sports Energy','emoji_pack',7000,'⚽🏀🎯🥇🚀','sports'],
            ];
            foreach ($items as [$key,$ar,$en,$category,$price,$emojis,$tier]) {
                DB::table('store_items')->updateOrInsert(['key'=>$key],[
                    'name'=>json_encode(['ar'=>$ar,'en'=>$en], JSON_UNESCAPED_UNICODE),
                    'category'=>$category,
                    'price'=>$price,
                    'duration_days'=>null,
                    'payload'=>json_encode(['icon'=>mb_substr($emojis,0,2),'emojis'=>$emojis,'tier'=>$tier,'tab'=>$tier,'animated'=>true,'large'=>true], JSON_UNESCAPED_UNICODE),
                    'active'=>true,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_auth_sessions');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('club_announcements');
        Schema::dropIfExists('progression_events');
    }
};
