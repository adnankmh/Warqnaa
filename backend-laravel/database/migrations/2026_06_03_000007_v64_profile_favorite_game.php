<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { if(Schema::hasTable('profiles') && !Schema::hasColumn('profiles','favorite_game_key')) Schema::table('profiles', fn(Blueprint $t)=>$t->string('favorite_game_key')->nullable()->after('country_name')); }
 public function down(): void { if(Schema::hasColumn('profiles','favorite_game_key')) Schema::table('profiles', fn(Blueprint $t)=>$t->dropColumn('favorite_game_key')); }
};
