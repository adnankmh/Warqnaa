<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('site_settings')){
   Schema::create('site_settings', function(Blueprint $t){
    $t->id();
    $t->string('key')->unique();
    $t->text('value')->nullable();
    $t->string('type')->default('string');
    $t->string('group')->default('general');
    $t->string('label')->nullable();
    $t->timestamps();
   });
  }
  $now=now();
  $defaults=[
   ['default_theme','royal','string','appearance','الثيم الافتراضي'],
   ['force_global_theme','0','bool','appearance','تطبيق ثيم الإدارة على الجميع'],
   ['store_enabled','1','bool','modules','تشغيل المتجر'],
   ['clubs_enabled','1','bool','modules','تشغيل النوادي'],
   ['tournaments_enabled','1','bool','modules','تشغيل المسابقات'],
   ['chat_enabled','1','bool','modules','تشغيل الدردشة'],
   ['support_enabled','1','bool','modules','تشغيل الدعم'],
   ['homepage_headline','Warqnaa','string','content','عنوان الصفحة الرئيسية'],
   ['maintenance_message','الموقع قيد التطوير والتحسين','string','content','رسالة الصيانة'],
  ];
  foreach($defaults as [$key,$value,$type,$group,$label]) DB::table('site_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'type'=>$type,'group'=>$group,'label'=>$label,'created_at'=>$now,'updated_at'=>$now]);
 }
 public function down(): void { Schema::dropIfExists('site_settings'); }
};
