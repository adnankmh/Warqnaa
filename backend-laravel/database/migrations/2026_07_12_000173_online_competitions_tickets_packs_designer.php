<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profiles')) {
            if (!Schema::hasColumn('profiles', 'pasha_style')) {
                $afterPashaDays = Schema::hasColumn('profiles', 'pasha_days');
                Schema::table('profiles', function (Blueprint $table) use ($afterPashaDays) {
                    $column = $table->string('pasha_style', 32)->default('red');
                    if ($afterPashaDays) $column->after('pasha_days');
                });
            }
            if (!Schema::hasColumn('profiles', 'champion_rank_points')) {
                $afterClubPoints = Schema::hasColumn('profiles', 'club_points');
                Schema::table('profiles', function (Blueprint $table) use ($afterClubPoints) {
                    $column = $table->unsignedBigInteger('champion_rank_points')->default(0);
                    if ($afterClubPoints) $column->after('club_points');
                });
            }
        }

        if (!Schema::hasTable('competition_tickets')) {
            Schema::create('competition_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('denomination');
                $table->unsignedInteger('quantity')->default(0);
                $table->unsignedInteger('total_used')->default(0);
                $table->timestamps();
                $table->unique(['user_id', 'denomination']);
                $table->index(['user_id', 'quantity']);
            });
        }

        if (!Schema::hasTable('daily_pack_claims')) {
            Schema::create('daily_pack_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('claim_date');
                $table->string('reward_type', 50);
                $table->string('reward_key', 150)->nullable();
                $table->unsignedInteger('duration_hours')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'claim_date']);
            });
        }

        if (!Schema::hasTable('challenge_definitions')) {
            Schema::create('challenge_definitions', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->json('name');
                $table->json('description')->nullable();
                $table->string('cadence', 24)->default('daily');
                $table->string('metric', 80);
                $table->unsignedInteger('target')->default(1);
                $table->unsignedBigInteger('reward_tokens')->default(0);
                $table->unsignedInteger('reward_xp')->default(0);
                $table->json('settings')->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('challenge_progress')) {
            Schema::create('challenge_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('challenge_definition_id')->constrained('challenge_definitions')->cascadeOnDelete();
                $table->unsignedInteger('progress')->default(0);
                $table->string('period_key', 32);
                $table->timestamp('claimed_at')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'challenge_definition_id', 'period_key'], 'challenge_progress_period_unique');
            });
        }

        if (!Schema::hasTable('admin_designer_entities')) {
            Schema::create('admin_designer_entities', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 80);
                $table->string('key', 150);
                $table->string('locale', 10)->default('all');
                $table->json('payload');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('revision')->default(1);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['entity_type', 'key', 'locale'], 'designer_entity_unique');
                $table->index(['entity_type', 'active', 'sort_order']);
            });
        }

        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', function (Blueprint $table) {
                if (!Schema::hasColumn('tournaments', 'key')) $table->string('key', 120)->nullable()->unique()->after('id');
                if (!Schema::hasColumn('tournaments', 'name')) $table->json('name')->nullable();
                if (!Schema::hasColumn('tournaments', 'description')) $table->json('description')->nullable();
                if (!Schema::hasColumn('tournaments', 'max_players')) $table->unsignedInteger('max_players')->default(64);
                if (!Schema::hasColumn('tournaments', 'rounds')) $table->unsignedInteger('rounds')->default(4);
                if (!Schema::hasColumn('tournaments', 'starts_at')) $table->timestamp('starts_at')->nullable();
                if (!Schema::hasColumn('tournaments', 'auto_accept')) $table->boolean('auto_accept')->default(true);
                if (!Schema::hasColumn('tournaments', 'random_seating')) $table->boolean('random_seating')->default(true);
                if (!Schema::hasColumn('tournaments', 'chat_enabled')) $table->boolean('chat_enabled')->default(true);
                if (!Schema::hasColumn('tournaments', 'turn_seconds')) $table->unsignedInteger('turn_seconds')->default(10);
                if (!Schema::hasColumn('tournaments', 'entry_mode')) $table->string('entry_mode', 30)->default('ticket_or_tokens');
                if (!Schema::hasColumn('tournaments', 'ad_entry_enabled')) $table->boolean('ad_entry_enabled')->default(false);
                if (!Schema::hasColumn('tournaments', 'featured')) $table->boolean('featured')->default(false);
                if (!Schema::hasColumn('tournaments', 'settings')) $table->json('settings')->nullable();
            });
        }

        if (Schema::hasTable('tournament_entries')) {
            Schema::table('tournament_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('tournament_entries', 'entry_mode')) $table->string('entry_mode', 30)->nullable();
                if (!Schema::hasColumn('tournament_entries', 'ticket_denomination')) $table->unsignedBigInteger('ticket_denomination')->nullable();
                if (!Schema::hasColumn('tournament_entries', 'paid_tokens')) $table->unsignedBigInteger('paid_tokens')->default(0);
                if (!Schema::hasColumn('tournament_entries', 'seed')) $table->unsignedInteger('seed')->nullable();
            });
        }

        if (Schema::hasTable('challenge_definitions')) {
            $challenges = [
                ['key'=>'daily_wins','name'=>['ar'=>'سلسلة النار','en'=>'Fire Streak'],'description'=>['ar'=>'حقق 3 انتصارات اليوم من دون انسحاب','en'=>'Win 3 games today without leaving'],'cadence'=>'daily','metric'=>'wins','target'=>3,'reward_tokens'=>750,'reward_xp'=>150,'sort_order'=>10],
                ['key'=>'clean_play','name'=>['ar'=>'اللعب النظيف','en'=>'Clean Play'],'description'=>['ar'=>'أكمل 5 مباريات بلا مغادرة أو بلاغ','en'=>'Complete 5 games without leaving or reports'],'cadence'=>'daily','metric'=>'clean_games','target'=>5,'reward_tokens'=>900,'reward_xp'=>180,'sort_order'=>20],
                ['key'=>'tarneeb_master','name'=>['ar'=>'سيّد الطرنيب','en'=>'Tarneeb Master'],'description'=>['ar'=>'اربح جولتين بفارق 10 نقاط','en'=>'Win two rounds by 10 points'],'cadence'=>'weekly','metric'=>'tarneeb_big_wins','target'=>2,'reward_tokens'=>1200,'reward_xp'=>250,'sort_order'=>30],
                ['key'=>'social','name'=>['ar'=>'تحدي الأصدقاء','en'=>'Friends Challenge'],'description'=>['ar'=>'العب 3 مباريات مع أصدقاء مختلفين','en'=>'Play 3 games with different friends'],'cadence'=>'weekly','metric'=>'friend_games','target'=>3,'reward_tokens'=>600,'reward_xp'=>120,'sort_order'=>40],
                ['key'=>'club','name'=>['ar'=>'قوة المجموعة','en'=>'Club Power'],'description'=>['ar'=>'اجمع 25 نقطة لمجموعتك خلال أسبوع','en'=>'Earn 25 club points this week'],'cadence'=>'weekly','metric'=>'club_points','target'=>25,'reward_tokens'=>2000,'reward_xp'=>400,'sort_order'=>50],
                ['key'=>'legend','name'=>['ar'=>'مسار الأسطورة','en'=>'Legend Path'],'description'=>['ar'=>'اربح 10 مباريات مصنفة هذا الموسم','en'=>'Win 10 ranked games this season'],'cadence'=>'seasonal','metric'=>'ranked_wins','target'=>10,'reward_tokens'=>5000,'reward_xp'=>1000,'sort_order'=>60],
            ];
            foreach ($challenges as $challenge) {
                DB::table('challenge_definitions')->updateOrInsert(['key'=>$challenge['key']], [
                    ...$challenge,
                    'name'=>json_encode($challenge['name'], JSON_UNESCAPED_UNICODE),
                    'description'=>json_encode($challenge['description'], JSON_UNESCAPED_UNICODE),
                    'settings'=>json_encode(['anti_abuse'=>true,'requires_completed_match'=>true]),
                    'active'=>true,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
        if (Schema::hasTable('admin_designer_entities')) {
            foreach ([
                ['entity_type'=>'system','key'=>'online_only','payload'=>['enabled'=>true,'offline_login'=>false,'offline_gameplay'=>false]],
                ['entity_type'=>'ads','key'=>'rewarded_ads','payload'=>['enabled'=>true,'daily_limit'=>5,'competition_entry'=>true]],
                ['entity_type'=>'daily_pack','key'=>'default_weights','payload'=>['once_per_day'=>true,'server_authoritative'=>true]],
                ['entity_type'=>'store','key'=>'competition_tickets','payload'=>['discount_percent'=>10,'denominations'=>[50,100,200,500,1000,2000,4000,5000,8000,10000,20000,30000,50000,100000]]],
            ] as $entity) {
                DB::table('admin_designer_entities')->updateOrInsert(
                    ['entity_type'=>$entity['entity_type'],'key'=>$entity['key'],'locale'=>'all'],
                    ['payload'=>json_encode($entity['payload'], JSON_UNESCAPED_UNICODE),'sort_order'=>0,'active'=>true,'revision'=>1,'created_at'=>now(),'updated_at'=>now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_progress');
        Schema::dropIfExists('challenge_definitions');
        Schema::dropIfExists('daily_pack_claims');
        Schema::dropIfExists('competition_tickets');
        Schema::dropIfExists('admin_designer_entities');
        if (Schema::hasTable('profiles')) {
            Schema::table('profiles', function (Blueprint $table) {
                foreach (['pasha_style','champion_rank_points'] as $column) if (Schema::hasColumn('profiles', $column)) $table->dropColumn($column);
            });
        }
    }
};
