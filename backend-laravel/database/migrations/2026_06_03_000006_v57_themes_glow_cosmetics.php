<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (Schema::hasTable('profiles')) {
   Schema::table('profiles', function(Blueprint $t){
    if (!Schema::hasColumn('profiles','active_site_theme')) $t->string('active_site_theme')->default('royal')->after('active_effect');
   });
  }
 }
 public function down(): void {
  if (Schema::hasTable('profiles') && Schema::hasColumn('profiles','active_site_theme')) {
   Schema::table('profiles', fn(Blueprint $t)=>$t->dropColumn('active_site_theme'));
  }
 }
};
