<?php
namespace App\Http\Controllers;

use App\Models\{User,Room,Tournament,Club,Game,AntiCheatEvent,Notification,StoreItem,SiteSetting,Friendship};
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminController
{
    private function guard(){ abort_unless(auth()->user()?->is_admin,403); }

    public function index()
    {
        $this->guard();
        $storeItems=StoreItem::orderBy('category')->orderBy('price')->get();
        $siteSettings=SiteSetting::all()->keyBy('key');
        return view('admin.index',[
            'users'=>User::with('profile','wallet')->latest()->paginate(50),
            'openRooms'=>Room::with('game','players.user.profile')->whereIn('status',['waiting','bidding','playing'])->latest()->get(),
            'openClubs'=>Club::with('owner','members.user.profile')->latest()->get(),
            'openTournaments'=>Tournament::with('game','creator','entries.user.profile')->whereIn('status',['open','running'])->latest()->get(),
            'rooms'=>Room::count(),'clubs'=>Club::count(),'tournaments'=>Tournament::count(),
            'antiCheat'=>AntiCheatEvent::latest()->limit(20)->get(),
            'supportMessages'=>Notification::where('type','support')->latest()->limit(40)->get(),
            'games'=>Game::orderBy('id')->get(),
            'allRooms'=>Room::with('game','owner','players.user.profile')->latest()->limit(120)->get(),
            'allClubs'=>Club::with('owner','members.user.profile')->latest()->limit(120)->get(),
            'allTournaments'=>Tournament::with('game','creator','entries.user.profile')->latest()->limit(120)->get(),
            'storeItems'=>$storeItems,
            'storeGroups'=>$storeItems->groupBy('category'),
            'siteSettings'=>$siteSettings,
            'themeOptions'=>$this->themeOptions(),
            'categoryLabels'=>$this->categoryLabels(),
        ]);
    }

    public function userAction(User $user, Request $r, WalletService $wallet)
    {
        $this->guard();
        $data=$r->validate([
            'action'=>['required', Rule::in(['ban','unban','admin','credit','reset_password','delete','update_profile','friend_request'])],
            'amount'=>'nullable|integer|min:1|max:1000000000',
            'password'=>'nullable|string|min:8|max:120','display_name'=>'nullable|string|max:80','level'=>'nullable|integer|min:1|max:999','xp'=>'nullable|integer|min:0|max:1000000000','name_color'=>'nullable|string|max:30',
        ]);
        $action=$data['action'];
        if($action==='ban') $user->update(['is_banned'=>true]);
        if($action==='unban') $user->update(['is_banned'=>false]);
        if($action==='admin') $user->update(['is_admin'=>true]);
        if($action==='credit') $wallet->credit($user,(int)($data['amount'] ?? 0),'admin_silent_gift',['visible_sender'=>false]);
        if($action==='reset_password') $user->update(['password'=>Hash::make($data['password'] ?? 'Warqna12345')]);

        if($action==='update_profile'){
            if(!$user->profile) $user->profile()->create(['display_name'=>$user->username,'country_code'=>'PS','country_name'=>'Palestine','level'=>1,'xp'=>0]);
            $user->profile->update([
                'display_name'=>$data['display_name'] ?? $user->profile->display_name,
                'level'=>$data['level'] ?? $user->profile->level,
                'xp'=>$data['xp'] ?? $user->profile->xp,
                'name_color'=>$data['name_color'] ?? $user->profile->name_color,
            ]);
        }
        if($action==='friend_request'){
            if($user->id!==auth()->id()){
                Friendship::updateOrCreate(
                    ['requester_id'=>auth()->id(),'addressee_id'=>$user->id],
                    ['status'=>'accepted']
                );
            }
        }

        if($action==='delete'){
            abort_if($user->is_admin || $user->id===auth()->id(),403,'لا يمكن حذف حساب إداري أو حسابك الحالي.');
            $user->delete();
            return redirect()->route('admin')->with('ok','تم حذف اللاعب نهائيًا');
        }
        return back()->with('ok','تم تنفيذ الإجراء');
    }

    public function saveSite(Request $r)
    {
        $this->guard();
        $themes=array_keys($this->themeOptions());
        $data=$r->validate([
            'default_theme'=>'required|in:'.implode(',',$themes),
            'force_global_theme'=>'nullable|boolean',
            'store_enabled'=>'nullable|boolean','clubs_enabled'=>'nullable|boolean','tournaments_enabled'=>'nullable|boolean','chat_enabled'=>'nullable|boolean','support_enabled'=>'nullable|boolean','auto_start_game'=>'nullable|boolean','round_score_popup'=>'nullable|boolean','table_uploads_enabled'=>'nullable|boolean','card_back_uploads_enabled'=>'nullable|boolean','tarneeb_only_panel'=>'nullable|boolean','large_bot_seats'=>'nullable|boolean',
            'homepage_headline'=>'nullable|string|max:120','hero_subtitle'=>'nullable|string|max:240','global_announcement'=>'nullable|string|max:240','maintenance_message'=>'nullable|string|max:240','nav_labels_json'=>'nullable|string|max:4000','homepage_cards_json'=>'nullable|string|max:6000','custom_css'=>'nullable|string|max:6000','layout_density'=>'nullable|in:compact,comfortable,wide','nav_style'=>'nullable|in:bar,glass,side','card_style'=>'nullable|in:rounded,luxury,flat','default_locale'=>'nullable|in:ar,en,fr,tr,de,es','store_layout'=>'nullable|in:tabs,cards,admin_grid','card_visual_size'=>'nullable|in:normal,large,compact','notifications_style'=>'nullable|in:panel,compact,wide',
        ]);
        SiteSetting::setValue('default_theme',$data['default_theme'],'string','appearance','الثيم الافتراضي');
        foreach(['force_global_theme','store_enabled','clubs_enabled','tournaments_enabled','chat_enabled','support_enabled','auto_start_game','round_score_popup','table_uploads_enabled','card_back_uploads_enabled','tarneeb_only_panel','large_bot_seats'] as $key) SiteSetting::setValue($key,$r->boolean($key),'bool','modules',$key);
        SiteSetting::setValue('homepage_headline',$data['homepage_headline'] ?? 'Warqna Zone','string','content','عنوان الصفحة الرئيسية');
        SiteSetting::setValue('hero_subtitle',$data['hero_subtitle'] ?? 'منصة ألعاب ورق اجتماعية احترافية','string','content','النص الفرعي الرئيسي');
        SiteSetting::setValue('global_announcement',$data['global_announcement'] ?? '','string','content','إعلان عام أعلى الموقع');
        SiteSetting::setValue('maintenance_message',$data['maintenance_message'] ?? '','string','content','رسالة الصيانة');
        foreach(['nav_labels_json','homepage_cards_json'] as $key) SiteSetting::setValue($key,$data[$key] ?? '', 'string','builder',$key);
        SiteSetting::setValue('custom_css',$this->sanitizeCustomCss($data['custom_css'] ?? ''), 'string','builder','custom_css');
        foreach(['layout_density','nav_style','card_style','default_locale','store_layout','card_visual_size','notifications_style'] as $key){ SiteSetting::setValue($key,$data[$key] ?? '', 'string','layout',$key); }
        return back()->with('ok','تم حفظ إعدادات الموقع العامة من لوحة الإدارة');
    }

    public function createStoreItem(Request $r)
    {
        $this->guard();
        $data=$this->validateStoreItem($r,true);
        $payload=$this->buildPayload($r);
        StoreItem::create([
            'key'=>$data['key'] ?: Str::slug($data['category'].'-'.$data['name_ar'].'-'.time()),
            'name'=>['ar'=>$data['name_ar'],'en'=>$data['name_en'] ?: $data['name_ar']],
            'category'=>$data['category'],'price'=>$data['price'],'duration_days'=>$data['duration_days'],'payload'=>$payload,'active'=>$r->boolean('active'),
        ]);
        return back()->with('ok','تمت إضافة المقتنى إلى المتجر لجميع اللاعبين');
    }

    public function updateStoreItem(StoreItem $item, Request $r)
    {
        $this->guard();
        $data=$this->validateStoreItem($r,false);
        $payload=$this->buildPayload($r,$item->payload ?: []);
        $item->update([
            'name'=>['ar'=>$data['name_ar'],'en'=>$data['name_en'] ?: $data['name_ar']],
            'category'=>$data['category'],'price'=>$data['price'],'duration_days'=>$data['duration_days'],'payload'=>$payload,'active'=>$r->boolean('active'),
        ]);
        return back()->with('ok','تم تعديل المقتنى بنجاح');
    }

    public function deleteStoreItem(StoreItem $item)
    {
        $this->guard();
        $item->update(['active'=>false]);
        return back()->with('ok','تم إخفاء المقتنى من المتجر. بقي محفوظًا لمن اشتراه سابقًا.');
    }

    private function validateStoreItem(Request $r, bool $create): array
    {
        return $r->validate([
            'key'=>($create?'nullable':'nullable').'|string|max:120',
            'name_ar'=>'required|string|max:120','name_en'=>'nullable|string|max:120',
            'category'=>'required|in:name_color,text_color,pasha,xp_booster,table,badge,card_back,name_frame,effect,emoji_pack',
            'price'=>'required|integer|min:0|max:999999999','duration_days'=>'nullable|integer|min:1|max:3650',
            'active'=>'nullable|boolean','tab'=>'nullable|string|max:50','tier'=>'nullable|string|max:50','preview_icon'=>'nullable|string|max:40','css_class'=>'nullable|string|max:120','color'=>'nullable|regex:/^#?[0-9a-fA-F]{3,8}$/','multiplier'=>'nullable|numeric|min:1|max:20','emojis'=>'nullable|string|max:400','asset'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
    }

    private function buildPayload(Request $r, array $old=[]): array
    {
        $payload=$old;
        foreach(['tab','tier','preview_icon','emojis'] as $k) if($r->filled($k)) $payload[$k]=strip_tags((string)$r->input($k));
        if($r->filled('color')) $payload['color']=str_starts_with($r->input('color'),'#') ? $r->input('color') : '#'.$r->input('color');
        if($r->hasFile('asset')){
            $dir=public_path('uploads/cosmetics'); if(!is_dir($dir)) mkdir($dir,0775,true);
            $file=$r->file('asset');
            $name='cosmetic_'.time().'_'.preg_replace('/[^a-zA-Z0-9_.-]/','',$file->getClientOriginalName());
            $file->move($dir,$name);
            $payload['asset_url']='/uploads/cosmetics/'.$name;
            if($r->input('category')==='table') $payload['table_image']='/uploads/cosmetics/'.$name;
            if($r->input('category')==='card_back') $payload['card_back_image']='/uploads/cosmetics/'.$name;
        }
        if($r->filled('css_class')){
            $css=preg_replace('/[^a-zA-Z0-9_\- ]/','',(string)$r->input('css_class'));
            $cat=$r->input('category');
            if($cat==='table') $payload['table']=$css;
            elseif($cat==='card_back') $payload['card_back']=$css;
            elseif($cat==='effect') $payload['effect']=$css;
            elseif($cat==='badge') $payload['badge']=$css;
            elseif($cat==='name_frame') $payload['frame']=$css;
            else $payload['css']=$css;
        }
        if($r->filled('multiplier')) $payload['multiplier']=(float)$r->input('multiplier');
        if($r->input('category')==='xp_booster') $payload['valid_days']=10;
        return $payload;
    }

    public function saveDesign(Request $r)
    {
        $this->guard();
        $numericRules=[
            'ui_button_width'=>[70,360,126],'ui_button_height'=>[32,96,46],'ui_button_radius'=>[0,44,16],'ui_button_font'=>[10,24,14],'ui_button_gap'=>[2,28,8],
            'ui_card_radius'=>[4,54,24],'ui_card_padding'=>[8,44,18],'ui_card_gap'=>[6,34,16],'ui_card_min_height'=>[120,460,220],
            'ui_page_padding'=>[6,36,18],'ui_page_max_width'=>[960,1920,1500],'ui_nav_height'=>[42,96,60],'ui_nav_radius'=>[0,34,16],
            'ui_store_card_width'=>[140,420,220],'ui_store_card_height'=>[150,520,270],'ui_store_icon_size'=>[32,150,72],
            'ui_game_card_width'=>[150,440,230],'ui_game_card_height'=>[140,420,230],'ui_game_icon_size'=>[30,130,64],
            'ui_table_radius'=>[16,96,46],'ui_table_border'=>[4,34,16],'ui_table_min_height'=>[420,980,610],'ui_table_center_scale'=>[60,130,92],
            'ui_card_play_width'=>[34,100,58],'ui_card_play_height'=>[48,145,82],'ui_player_avatar'=>[34,96,56],
            'ui_chat_width'=>[240,720,340],'ui_chat_height'=>[300,820,560],'ui_chat_radius'=>[0,42,24],'ui_chat_font'=>[10,22,14],'ui_chat_button_width'=>[34,220,82],'ui_chat_button_height'=>[28,76,40],'ui_chat_button_radius'=>[0,34,14],'ui_chat_input_height'=>[30,72,44],'ui_chat_emoji_size'=>[20,86,34],'ui_chat_gap'=>[2,22,8],'ui_notif_width'=>[260,720,420],
            'ui_profile_width'=>[320,840,560],'ui_profile_font'=>[10,20,13],
            'xp_no_pasha_per_round'=>[0,1000,10],'xp_pasha_per_round'=>[0,2000,20],'exit_penalty_xp'=>[0,5000,200],
        ];
        $colors=[
            'ui_button_bg'=>'#2e225f','ui_button_text'=>'#ffffff','ui_primary_bg'=>'#facc15','ui_primary_bg2'=>'#ec4899','ui_panel_bg'=>'#0f172a','ui_card_bg'=>'#1e293b','ui_site_bg1'=>'#07170f','ui_site_bg2'=>'#020617','ui_table_bg1'=>'#16a34a','ui_table_bg2'=>'#064e3b','ui_table_border_color'=>'#5b3718','ui_store_price_color'=>'#facc15','ui_nav_bg'=>'#020617','ui_chat_bg'=>'#0f172a','ui_chat_header_bg'=>'#312e81','ui_chat_button_bg'=>'#2e225f','ui_chat_button_text'=>'#ffffff','ui_chat_input_bg'=>'#020617','ui_chat_message_bg'=>'#1e293b'
        ];
        foreach($numericRules as $key=>[$min,$max,$default]){
            $value=(int) $r->input($key,$default);
            $value=max($min,min($max,$value));
            SiteSetting::setValue($key,$value,'int','designer',$key);
        }
        foreach($colors as $key=>$default){
            $value=(string)$r->input($key,$default);
            if(!preg_match('/^#[0-9a-fA-F]{6}$/',$value)) $value=$default;
            SiteSetting::setValue($key,$value,'string','designer',$key);
        }
        foreach(['ui_button_style'=>['solid','gradient','glass','outline'],'ui_card_shadow'=>['soft','medium','strong','none'],'ui_table_shape'=>['rounded','stadium','square-soft'],'ui_store_layout_mode'=>['grid','compact','showcase'],'ui_animation_level'=>['none','soft','premium']] as $key=>$allowed){
            $value=(string)$r->input($key,$allowed[0]);
            if(!in_array($value,$allowed,true)) $value=$allowed[0];
            SiteSetting::setValue($key,$value,'string','designer',$key);
        }
        foreach(['single_activity_lock_enabled','room_owner_password_invites','pasha_kick_dropdown_enabled','exit_penalty_dropdown_enabled','autoplay_timeout_enabled','admin_live_preview_enabled'] as $key){
            SiteSetting::setValue($key,$r->boolean($key),'bool','gameplay',$key);
        }
        return back()->with('ok','تم حفظ مصمم الموقع الشامل وتطبيقه على الواجهة والمتجر وغرف اللعب بدون تعديل أكواد');
    }

    public function createGame(Request $r)
    {
        $this->guard();
        $data=$r->validate([
            'key'=>'required|string|max:60|unique:games,key','name_ar'=>'required|string|max:120','name_en'=>'nullable|string|max:120',
            'min_players'=>'required|integer|min:1|max:8','max_players'=>'required|integer|min:1|max:8','partnership'=>'nullable|boolean','active'=>'nullable|boolean',
            'icon'=>'nullable|string|max:12','family'=>'nullable|string|max:60','engine'=>'nullable|string|max:100','rules_ar'=>'nullable|string|max:10000','rules_en'=>'nullable|string|max:10000'
        ]);
        $rules=['icon'=>$data['icon'] ?? '🃏','family'=>$data['family'] ?? 'cards','engine'=>$data['engine'] ?? 'UniversalSocialGameRules','rules_ar'=>$data['rules_ar'] ?? '','rules_en'=>$data['rules_en'] ?? ''];
        Game::create(['key'=>$data['key'],'name'=>['ar'=>$data['name_ar'],'en'=>$data['name_en'] ?: $data['name_ar']],'min_players'=>$data['min_players'],'max_players'=>$data['max_players'],'partnership'=>$r->boolean('partnership'),'rules'=>$rules,'active'=>$r->boolean('active')]);
        return back()->with('ok','تمت إضافة لعبة جديدة وتفعيلها حسب اختيارك');
    }

    public function updateGame(Game $game, Request $r)
    {
        $this->guard();
        $data=$r->validate([
            'name_ar'=>'required|string|max:120','name_en'=>'nullable|string|max:120','min_players'=>'required|integer|min:1|max:8','max_players'=>'required|integer|min:1|max:8',
            'partnership'=>'nullable|boolean','active'=>'nullable|boolean','icon'=>'nullable|string|max:12','family'=>'nullable|string|max:60','engine'=>'nullable|string|max:100',
            'rules_ar'=>'nullable|string|max:10000','rules_en'=>'nullable|string|max:10000','rules_json'=>'nullable|string|max:20000',
            'xp_no_pasha'=>'nullable|integer|min:0|max:1000','xp_pasha'=>'nullable|integer|min:0|max:2000','exit_penalty'=>'nullable|integer|min:0|max:5000'
        ]);
        $rules=$game->rules ?: [];
        if($r->filled('rules_json')){
            $decoded=json_decode($r->input('rules_json'),true);
            if(!is_array($decoded)) return back()->withErrors(['rules_json'=>'JSON القوانين غير صحيح']);
            $rules=array_merge($rules,$decoded);
        }
        $rules['icon']=$data['icon'] ?? ($rules['icon'] ?? '🃏');
        $rules['family']=$data['family'] ?? ($rules['family'] ?? 'cards');
        $rules['engine']=$data['engine'] ?? ($rules['engine'] ?? 'UniversalSocialGameRules');
        $rules['rules_ar']=$data['rules_ar'] ?? '';
        $rules['rules_en']=$data['rules_en'] ?? '';
        $rules['xp_no_pasha']=(int)($data['xp_no_pasha'] ?? SiteSetting::getValue('xp_no_pasha_per_round',10));
        $rules['xp_pasha']=(int)($data['xp_pasha'] ?? SiteSetting::getValue('xp_pasha_per_round',20));
        $rules['exit_penalty']=(int)($data['exit_penalty'] ?? SiteSetting::getValue('exit_penalty_xp',200));
        $game->update(['name'=>['ar'=>$data['name_ar'],'en'=>$data['name_en'] ?: $data['name_ar']],'min_players'=>$data['min_players'],'max_players'=>$data['max_players'],'partnership'=>$r->boolean('partnership'),'active'=>$r->boolean('active'),'rules'=>$rules]);
        return back()->with('ok','تم حفظ اللعبة وقوانينها وإعداداتها');
    }

    public function updateRoom(Room $room, Request $r)
    {
        $this->guard();
        $data=$r->validate([
            'visibility'=>'required|in:public,friends,private','status'=>'required|in:waiting,bidding,playing,finished,closed','max_players'=>'required|integer|min:1|max:8',
            'entry_fee'=>'nullable|integer|min:0|max:100000000','min_level'=>'nullable|integer|min:1|max:999','target_score'=>'nullable|string|max:40','password'=>'nullable|string|max:80'
        ]);
        $room->update(['visibility'=>$data['visibility'],'status'=>$data['status'],'max_players'=>$data['max_players'],'entry_fee'=>$data['entry_fee'] ?? 0,'min_level'=>$data['min_level'] ?? 1,'target_score'=>$data['target_score'] ?? null,'password'=>$data['password'] ?: null]);
        return back()->with('ok','تم تحديث الغرفة من لوحة الإدارة');
    }

    public function closeRoom(Room $room)
    {
        $this->guard();
        $room->update(['status'=>'closed','finished_at'=>now()]);
        return back()->with('ok','تم إغلاق الغرفة');
    }

    public function updateClub(Club $club, Request $r)
    {
        $this->guard();
        $data=$r->validate(['name'=>'required|string|max:120','level'=>'required|integer|min:1|max:99','weekly_points'=>'required|integer|min:0|max:100000000','treasury'=>'required|integer|min:0|max:999999999999']);
        $club->update($data);
        return back()->with('ok','تم تحديث النادي');
    }

    public function deleteClub(Club $club)
    {
        $this->guard();
        $club->delete();
        return back()->with('ok','تم حذف النادي');
    }

    public function createTournament(Request $r)
    {
        $this->guard();
        $data=$r->validate(['game_id'=>'required|exists:games,id','stages'=>'required|integer|min:1|max:8','seats_per_match'=>'required|integer|min:2|max:8','entry_fee'=>'required|integer|min:0|max:100000000','prize_pool'=>'required|integer|min:0|max:999999999999','status'=>'required|in:open,running,finished,cancelled']);
        $data['creator_id']=auth()->id();
        Tournament::create($data);
        return back()->with('ok','تم إنشاء منافسة جديدة');
    }

    public function updateTournament(Tournament $tournament, Request $r)
    {
        $this->guard();
        $data=$r->validate(['stages'=>'required|integer|min:1|max:8','seats_per_match'=>'required|integer|min:2|max:8','entry_fee'=>'required|integer|min:0|max:100000000','prize_pool'=>'required|integer|min:0|max:999999999999','status'=>'required|in:open,running,finished,cancelled']);
        $tournament->update($data);
        return back()->with('ok','تم تحديث المنافسة');
    }

    public function deleteTournament(Tournament $tournament)
    {
        $this->guard();
        $tournament->update(['status'=>'cancelled']);
        return back()->with('ok','تم إلغاء المنافسة');
    }




    private function sanitizeCustomCss(string $css): string
    {
        $css=trim($css);
        $blocked=['</style','<script','javascript:','expression(','@import','behavior:','-moz-binding'];
        foreach($blocked as $needle){
            if(stripos($css,$needle)!==false) return '/* تم رفض CSS مخصص يحتوي على تعليمات غير آمنة */';
        }
        return mb_substr($css,0,6000);
    }

    private function categoryLabels(): array
    {
        return ['pasha'=>'الباشا','xp_booster'=>'مسرعات XP','table'=>'الطاولات','card_back'=>'ظهر الورق','text_color'=>'ألوان الكتابة','name_color'=>'ألوان اللاعبين وGlow','emoji_pack'=>'الإيموجي','badge'=>'الشارات','effect'=>'مؤثرات الفوز','name_frame'=>'إطارات الاسم'];
    }
    private function themeOptions(): array
    {
        return ['royal'=>'ملكي ذهبي','midnight'=>'ليلي أزرق','emerald'=>'زمردي','desert'=>'صحراوي','galaxy'=>'مجرة نيون','crimson'=>'قرمزي','ocean'=>'محيطي','obsidian'=>'أسود زجاجي','aurora'=>'شفق قطبي'];
    }
}
