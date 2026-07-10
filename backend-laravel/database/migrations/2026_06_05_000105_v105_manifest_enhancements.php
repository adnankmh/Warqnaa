<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if(Schema::hasTable('clubs')) Schema::table('clubs', function(Blueprint $t){
   if(!Schema::hasColumn('clubs','league_tier')) $t->string('league_tier')->default('bronze')->after('level');
   if(!Schema::hasColumn('clubs','total_points')) $t->unsignedBigInteger('total_points')->default(0)->after('weekly_points');
   if(!Schema::hasColumn('clubs','season_points')) $t->unsignedBigInteger('season_points')->default(0)->after('total_points');
   if(!Schema::hasColumn('clubs','capacity')) $t->unsignedInteger('capacity')->default(20)->after('treasury');
   if(!Schema::hasColumn('clubs','logo')) $t->string('logo')->nullable()->after('name');
   if(!Schema::hasColumn('clubs','description')) $t->text('description')->nullable()->after('logo');
  });
  if(Schema::hasTable('club_members')) Schema::table('club_members', function(Blueprint $t){
   if(!Schema::hasColumn('club_members','total_points')) $t->unsignedBigInteger('total_points')->default(0)->after('weekly_points');
   if(!Schema::hasColumn('club_members','last_active_at')) $t->timestamp('last_active_at')->nullable()->after('total_points');
  });
  if(Schema::hasTable('tournaments')) Schema::table('tournaments', function(Blueprint $t){
   if(!Schema::hasColumn('tournaments','house_cut_percent')) $t->unsignedTinyInteger('house_cut_percent')->default(10)->after('prize_pool');
   if(!Schema::hasColumn('tournaments','prize_distribution')) $t->json('prize_distribution')->nullable()->after('house_cut_percent');
   if(!Schema::hasColumn('tournaments','leaderboard_points')) $t->json('leaderboard_points')->nullable()->after('prize_distribution');
  });
 }
 public function down(): void {}
};
