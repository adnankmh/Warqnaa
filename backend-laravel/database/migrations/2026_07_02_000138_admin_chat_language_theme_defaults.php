<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if(!Schema::hasTable('site_settings')) return;
        $now=now();
        $defaults=[
            ['ui_chat_radius','24','int','designer','استدارة نافذة الدردشة'],
            ['ui_chat_font','14','int','designer','حجم خط الدردشة'],
            ['ui_chat_button_width','82','int','designer','عرض أزرار الدردشة'],
            ['ui_chat_button_height','40','int','designer','ارتفاع أزرار الدردشة'],
            ['ui_chat_button_radius','14','int','designer','استدارة أزرار الدردشة'],
            ['ui_chat_input_height','44','int','designer','ارتفاع خانة كتابة الدردشة'],
            ['ui_chat_emoji_size','34','int','designer','حجم إيموجز الدردشة'],
            ['ui_chat_gap','8','int','designer','المسافات داخل الدردشة'],
            ['ui_chat_header_bg','#312e81','string','designer','لون رأس الدردشة'],
            ['ui_chat_button_bg','#2e225f','string','designer','لون أزرار الدردشة'],
            ['ui_chat_button_text','#ffffff','string','designer','لون نص أزرار الدردشة'],
            ['ui_chat_input_bg','#020617','string','designer','لون خانة كتابة الدردشة'],
            ['ui_chat_message_bg','#1e293b','string','designer','لون رسائل الدردشة'],
            ['default_locale','ar','string','layout','لغة الموقع الافتراضية'],
            ['force_global_theme','0','bool','appearance','فرض الثيم على الجميع'],
        ];
        foreach($defaults as [$key,$value,$type,$group,$label]){
            DB::table('site_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'type'=>$type,'group'=>$group,'label'=>$label,'created_at'=>$now,'updated_at'=>$now]);
        }
    }
    public function down(): void {}
};
