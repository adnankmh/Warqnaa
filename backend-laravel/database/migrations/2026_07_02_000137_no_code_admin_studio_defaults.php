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
            ['ui_button_width','126','int','designer','عرض الأزرار'],['ui_button_height','46','int','designer','ارتفاع الأزرار'],['ui_button_radius','16','int','designer','استدارة الأزرار'],['ui_button_font','14','int','designer','حجم خط الأزرار'],['ui_button_gap','8','int','designer','المسافة بين الأزرار'],
            ['ui_card_radius','24','int','designer','استدارة البطاقات'],['ui_card_padding','18','int','designer','حشو البطاقات'],['ui_card_gap','16','int','designer','المسافة بين البطاقات'],['ui_card_min_height','220','int','designer','ارتفاع البطاقات'],
            ['ui_page_padding','18','int','designer','هوامش الصفحات'],['ui_page_max_width','1500','int','designer','أقصى عرض للصفحة'],['ui_nav_height','60','int','designer','ارتفاع النافبار'],['ui_nav_radius','16','int','designer','استدارة النافبار'],
            ['ui_store_card_width','220','int','designer','عرض عنصر المتجر'],['ui_store_card_height','270','int','designer','ارتفاع عنصر المتجر'],['ui_store_icon_size','72','int','designer','حجم أيقونة المتجر'],
            ['ui_game_card_width','230','int','designer','عرض بطاقة اللعبة'],['ui_game_card_height','230','int','designer','ارتفاع بطاقة اللعبة'],['ui_game_icon_size','64','int','designer','حجم أيقونة اللعبة'],
            ['ui_table_radius','46','int','designer','استدارة الطاولة'],['ui_table_border','16','int','designer','حافة الطاولة'],['ui_table_min_height','610','int','designer','ارتفاع الطاولة'],['ui_table_center_scale','92','int','designer','حجم مركز الطاولة'],
            ['ui_card_play_width','58','int','designer','عرض ورق اللعب'],['ui_card_play_height','82','int','designer','ارتفاع ورق اللعب'],['ui_player_avatar','56','int','designer','حجم صورة اللاعب'],
            ['ui_chat_width','320','int','designer','عرض الدردشة'],['ui_chat_height','560','int','designer','ارتفاع الدردشة'],['ui_notif_width','420','int','designer','عرض الإشعارات'],['ui_profile_width','560','int','designer','عرض البروفايل'],['ui_profile_font','13','int','designer','خط البروفايل'],
            ['xp_no_pasha_per_round','10','int','gameplay','XP بدون باشا'],['xp_pasha_per_round','20','int','gameplay','XP مع باشا'],['exit_penalty_xp','200','int','gameplay','خصم الخروج'],
            ['ui_button_bg','#2e225f','string','designer','لون الأزرار'],['ui_button_text','#ffffff','string','designer','لون نص الأزرار'],['ui_primary_bg','#facc15','string','designer','لون الزر الأساسي 1'],['ui_primary_bg2','#ec4899','string','designer','لون الزر الأساسي 2'],['ui_panel_bg','#0f172a','string','designer','لون اللوحات'],['ui_card_bg','#1e293b','string','designer','لون البطاقات'],['ui_site_bg1','#07170f','string','designer','خلفية الموقع 1'],['ui_site_bg2','#020617','string','designer','خلفية الموقع 2'],['ui_table_bg1','#16a34a','string','designer','لون الطاولة 1'],['ui_table_bg2','#064e3b','string','designer','لون الطاولة 2'],['ui_table_border_color','#5b3718','string','designer','لون حافة الطاولة'],['ui_store_price_color','#facc15','string','designer','لون سعر المتجر'],['ui_nav_bg','#020617','string','designer','لون النافبار'],['ui_chat_bg','#0f172a','string','designer','لون الدردشة'],
            ['ui_button_style','gradient','string','designer','شكل الأزرار'],['ui_card_shadow','medium','string','designer','ظل البطاقات'],['ui_table_shape','rounded','string','designer','شكل الطاولة'],['ui_store_layout_mode','grid','string','designer','عرض المتجر'],['ui_animation_level','soft','string','designer','الحركة'],
            ['single_activity_lock_enabled','1','bool','gameplay','منع تعدد الاشتراك'],['room_owner_password_invites','1','bool','gameplay','كلمة سر الدعوات'],['pasha_kick_dropdown_enabled','1','bool','gameplay','قائمة طرد الباشا'],['exit_penalty_dropdown_enabled','1','bool','gameplay','قائمة خصم الخروج'],['autoplay_timeout_enabled','1','bool','gameplay','اللعب التلقائي عند انتهاء الوقت'],['admin_live_preview_enabled','1','bool','designer','معاينة مباشرة للإدارة'],
        ];
        foreach($defaults as [$key,$value,$type,$group,$label]){
            DB::table('site_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'type'=>$type,'group'=>$group,'label'=>$label,'created_at'=>$now,'updated_at'=>$now]);
        }
    }
    public function down(): void {}
};
