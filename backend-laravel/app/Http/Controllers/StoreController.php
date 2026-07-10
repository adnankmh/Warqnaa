<?php
namespace App\Http\Controllers;

use App\Models\{StoreItem,InventoryItem,User};
use App\Services\Wallet\WalletService;
use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoreController
{
    public function index(StoreCatalogService $catalog)
    {
        $catalog->sync();
        if(class_exists('\\App\\Models\\SiteSetting') && !\App\Models\SiteSetting::getValue('store_enabled',true)) return view('store.index',['items'=>collect(),'inventory'=>auth()->user()->inventoryItems()->with('storeItem')->latest()->get(),'storeDisabled'=>true]);
        $allItems=StoreItem::where('active',true)->orderBy('category')->orderBy('price')->get();
        $grouped=$allItems->groupBy(function($item){ return $item->category==='name_frame' ? 'name_color' : $item->category; });
        return view('store.index', [
            'items'=>$grouped,
            'inventory'=>auth()->user()->inventoryItems()->with('storeItem')->latest()->get(),
        ]);
    }

    public function buy(StoreItem $item, WalletService $wallet)
    {
        if(class_exists('\\App\\Models\\SiteSetting') && !\App\Models\SiteSetting::getValue('store_enabled',true)) return $this->friendlyFail('المتجر متوقف مؤقتًا من الإدارة.');
        if(!$item->duration_days && in_array($item->category,['badge','table','card_back','name_color','text_color','effect'],true)) {
            if(auth()->user()->inventoryItems()->where('store_item_id',$item->id)->exists()) return $this->friendlyFail('هذا العنصر موجود لديك بالفعل. يمكنك تفعيله من مشترياتي.');
        }
        try {
            if($item->price>0) {
                $wallet->debit(auth()->user(),$item->price,'store_buy',['item'=>$item->key,'category'=>$item->category]);
                $admin=User::where('is_admin',true)->orderBy('id')->first();
                if($admin && $admin->id !== auth()->id()) {
                    $wallet->credit($admin,$item->price,'store_sale_income',['buyer'=>auth()->id(),'item'=>$item->key,'category'=>$item->category]);
                }
            }
        } catch(RuntimeException $e){ return $this->friendlyFail('رصيدك من التوكنز غير كافٍ. تحتاج إلى شراء توكنز أو ترقية مستواك للحصول على مكافآت.'); }
        if($item->category==='pasha'){
            $days=(int)($item->duration_days ?: (($item->payload ?: [])['days'] ?? 30));
            if(auth()->user()->profile){ auth()->user()->profile->increment('pasha_days',$days); }
            return $this->friendlyOk('✅ تم شراء '.$days.' يوم باشا. تم تفعيل ميزات الباشا: XP أعلى، أولوية بالغرف، صلاحيات VIP، وإمكانية إنشاء نوادي/منافسات عند توفر التوكنز.');
        }
        $inv=InventoryItem::create(['user_id'=>auth()->id(),'store_item_id'=>$item->id,'expires_at'=>(($item->payload ?: [])['valid_days'] ?? null) ? now()->addDays((int)(($item->payload ?: [])['valid_days'])) : null]);
        return $this->friendlyOk('✅ تم شراء '.($item->name['ar'] ?? $item->key).' بنجاح بدون تحديث الصفحة. تم خصم التوكنز وإضافة العنصر إلى مشترياتي للتفعيل.', ['inventory_id'=>$inv->id,'item'=>['id'=>$item->id,'name'=>$item->name['ar'] ?? $item->key,'category'=>$item->category,'key'=>$item->key,'payload'=>$item->payload ?: [],'duration_days'=>$item->duration_days],'category'=>$item->category,'payload'=>$item->payload ?: []]);
    }

    public function activate(InventoryItem $inventory)
    {
        abort_unless($inventory->user_id===auth()->id(),403);
        $item=$inventory->storeItem;
        DB::transaction(function() use($inventory,$item){
            // العناصر الشكلية: عنصر واحد مفعل من نفس القسم في نفس الوقت.
            if(in_array($item->category,['name_color','text_color','badge','table','xp_booster','card_back','name_frame','effect','emoji_pack'],true)) {
                InventoryItem::where('user_id',auth()->id())->whereHas('storeItem',fn($q)=>$q->where('category',$item->category))->update(['active'=>false]);
            }
            $activeDays = ($item->category==='xp_booster') ? 1 : ($item->duration_days ?: null);
            $inventory->update(['active'=>true,'activated_at'=>now(),'expires_at'=>$activeDays?now()->addDays($activeDays):$inventory->expires_at]);
            $payload=$item->payload ?: [];
            $profile=auth()->user()->profile;
            if($profile){
                if($item->category==='name_color' && isset($payload['color'])) { $profile->name_color=$payload['color']; $profile->active_name_frame=$payload['frame'] ?? $payload['glow'] ?? ('glow-'.str_replace('#','',$payload['color'])); }
                if($item->category==='text_color' && isset($payload['color'])) { $profile->chat_color=$payload['color']; $profile->text_color=$payload['color']; }
                if($item->category==='badge') $profile->badge=$payload['badge'] ?? $item->key;
                if($item->category==='table') $profile->active_table_skin=$payload['table'] ?? $item->key;
                if($item->category==='card_back') $profile->active_card_back=$payload['card_back'] ?? $item->key;
                if($item->category==='name_frame') { $profile->active_name_frame=$payload['frame'] ?? $item->key; if(isset($payload['color'])) $profile->name_color=$payload['color']; }
                if($item->category==='effect') $profile->active_effect=$payload['effect'] ?? $item->key;
                if($item->category==='xp_booster') $profile->xp_boost_multiplier=(float)($payload['multiplier'] ?? 1.25);
                $profile->save();
            }
        });
        return $this->friendlyOk('تم التفعيل بنجاح', ['activated'=>true,'category'=>$item->category,'payload'=>$item->payload ?: [],'inventory_id'=>$inventory->id]);
    }
    private function friendlyOk(string $message, array $extra=[]){ if(request()->expectsJson() || request()->ajax()) return response()->json(array_merge(['ok'=>true,'message'=>$message],$extra)); return back()->with('ok',$message); }
    private function friendlyFail(string $message){ if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>false,'message'=>$message],200); return back()->withErrors(['msg'=>$message]); }
}

