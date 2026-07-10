<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('profiles', function(Blueprint $t){
   $t->boolean('sound_enabled')->default(true)->after('xp_boost_multiplier');
   $t->string('active_card_back')->nullable()->after('active_table_skin');
   $t->string('active_name_frame')->nullable()->after('active_card_back');
   $t->string('active_effect')->nullable()->after('active_name_frame');
   $t->unsignedInteger('daily_streak')->default(0)->after('wins');
   $t->date('last_daily_reward_at')->nullable()->after('daily_streak');
  });
 }
 public function down(): void {
  Schema::table('profiles', fn(Blueprint $t)=>$t->dropColumn(['sound_enabled','active_card_back','active_name_frame','active_effect','daily_streak','last_daily_reward_at']));
 }
};
