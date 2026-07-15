<?php
namespace App\Http\Controllers;

use App\Models\{CompetitionTicket,StoreItem,InventoryItem,User};
use App\Services\Wallet\WalletService;
use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Support\Facades\{DB,Log};
use RuntimeException;
use Throwable;

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
        if (class_exists('\\App\\Models\\SiteSetting') && !\App\Models\SiteSetting::getValue('store_enabled', true)) {
            return $this->friendlyFail('المتجر متوقف مؤقتًا من الإدارة.');
        }

        $user = auth()->user();
        if (!$item->duration_days && in_array($item->category, ['badge','table','pasha_style','card_back','name_color','text_color','effect','profile_cover'], true)) {
            if ($user->inventoryItems()->where('store_item_id', $item->id)->exists()) {
                return $this->friendlyFail('هذا العنصر موجود لديك بالفعل. يمكنك تفعيله من مشترياتي.');
            }
        }

        $payload = $item->payload ?: [];
        $ticketDenomination = $item->category === 'competition_ticket'
            ? (int)($payload['denomination'] ?? 0)
            : 0;
        if ($item->category === 'competition_ticket' && $ticketDenomination <= 0) {
            return $this->friendlyFail('فئة التذكرة غير صحيحة.');
        }

        try {
            $purchase = DB::transaction(function () use ($user, $item, $payload, $ticketDenomination, $wallet) {
                if ((int)$item->price > 0) {
                    $wallet->debit($user, (int)$item->price, 'store_buy', [
                        'item'=>$item->key,
                        'category'=>$item->category,
                    ]);
                    $wallet->creditPrimaryAdminRevenue($user, (int)$item->price, 'store_sale_income', [
                        'item'=>$item->key,
                        'category'=>$item->category,
                    ]);
                }

                if ($item->category === 'pasha') {
                    $days = (int)($item->duration_days ?: ($payload['days'] ?? 30));
                    $profile = $user->profile()->lockForUpdate()->firstOrCreate([], [
                        'display_name'=>$user->username,
                        'country_code'=>'PS',
                        'country_name'=>country_name('PS'),
                    ]);
                    $profile->increment('pasha_days', $days);
                    return ['kind'=>'pasha', 'days'=>$days];
                }

                if ($item->category === 'competition_ticket') {
                    $ticket = CompetitionTicket::firstOrCreate(
                        ['user_id'=>$user->id, 'denomination'=>$ticketDenomination],
                        ['quantity'=>0, 'total_used'=>0],
                    );
                    $ticket->increment('quantity');
                    return ['kind'=>'ticket', 'denomination'=>$ticketDenomination, 'quantity'=>(int)$ticket->fresh()->quantity];
                }

                $validDays = (int)($payload['valid_days'] ?? $item->duration_days ?? 0);
                $inventory = InventoryItem::create([
                    'user_id'=>$user->id,
                    'store_item_id'=>$item->id,
                    'expires_at'=>$validDays > 0 ? now()->addDays($validDays) : null,
                ]);
                return ['kind'=>'inventory', 'inventory_id'=>$inventory->id];
            });
        } catch (RuntimeException $e) {
            return $this->friendlyFail('رصيدك من التوكنز غير كافٍ. تحتاج إلى شراء توكنز أو ترقية مستواك للحصول على مكافآت.');
        } catch (Throwable $e) {
            Log::error('Warqnaa store purchase failed', [
                'user_id'=>(int)$user->id,
                'item_id'=>(int)$item->id,
                'error'=>$e->getMessage(),
            ]);
            return $this->friendlyFail('تعذر إتمام الشراء بأمان. لم يتم خصم أي مبلغ؛ حاول مرة أخرى بعد تحديث المتجر.');
        }

        if ($purchase['kind'] === 'pasha') {
            return $this->friendlyOk('✅ تم شراء '.$purchase['days'].' يوم باشا. تم تفعيل ميزات الباشا: XP أعلى، أولوية بالغرف، صلاحيات VIP، وإمكانية إنشاء نوادٍ ومنافسات.');
        }
        if ($purchase['kind'] === 'ticket') {
            return $this->friendlyOk('✅ تم شراء تذكرة منافسة بقيمة '.$purchase['denomination'].' توكنز.', [
                'ticket'=>['denomination'=>$purchase['denomination'], 'quantity'=>$purchase['quantity']],
            ]);
        }

        return $this->friendlyOk('✅ تم شراء '.($item->name['ar'] ?? $item->key).' بنجاح. تم خصم التوكنز وتحويل قيمة الشراء إلى حساب الإدارة وإضافة العنصر إلى مشترياتك.', [
            'inventory_id'=>$purchase['inventory_id'],
            'item'=>[
                'id'=>$item->id,
                'name'=>$item->name['ar'] ?? $item->key,
                'category'=>$item->category,
                'key'=>$item->key,
                'payload'=>$payload,
                'duration_days'=>$item->duration_days,
            ],
            'category'=>$item->category,
            'payload'=>$payload,
        ]);
    }

    public function activate(InventoryItem $inventory)
    {
        abort_unless($inventory->user_id===auth()->id(),403);
        $item=$inventory->storeItem;
        DB::transaction(function() use($inventory,$item){
            // العناصر الشكلية: عنصر واحد مفعل من نفس القسم في نفس الوقت.
            if(in_array($item->category,['name_color','text_color','badge','table','pasha_style','xp_booster','card_back','name_frame','effect','emoji_pack','profile_cover'],true)) {
                InventoryItem::where('user_id',auth()->id())->whereHas('storeItem',fn($q)=>$q->where('category',$item->category))->update(['active'=>false]);
            }
            $payload=$item->payload ?: [];
            $activeHours = ($item->category==='xp_booster') ? max(1,(int)($payload['activate_hours'] ?? 24)) : null;
            $activeDays = $item->category==='xp_booster' ? null : ($item->duration_days ?: null);
            $inventory->update(['active'=>true,'activated_at'=>now(),'expires_at'=>$activeHours?now()->addHours($activeHours):($activeDays?now()->addDays($activeDays):$inventory->expires_at)]);
            $profile=auth()->user()->profile;
            if($profile){
                if($item->category==='name_color' && isset($payload['color'])) { $profile->name_color=$payload['color']; $profile->active_name_frame=$payload['frame'] ?? $payload['glow'] ?? ('glow-'.str_replace('#','',$payload['color'])); }
                if($item->category==='text_color' && isset($payload['color'])) { $profile->chat_color=$payload['color']; $profile->text_color=$payload['color']; }
                if($item->category==='badge') $profile->badge=$payload['badge'] ?? $item->key;
                if($item->category==='table') $profile->active_table_skin=$payload['table'] ?? $item->key;
                if($item->category==='pasha_style') {
                    $profile->pasha_style=$payload['style'] ?? 'red';
                    if(isset($payload['color1'])) { $profile->name_color=$payload['color1']; $profile->chat_color=$payload['color1']; $profile->text_color=$payload['color1']; }
                }
                if($item->category==='card_back') $profile->active_card_back=$payload['card_back'] ?? $item->key;
                if($item->category==='name_frame') { $profile->active_name_frame=$payload['frame'] ?? $item->key; if(isset($payload['color'])) $profile->name_color=$payload['color']; }
                if($item->category==='effect') { if(isset($payload['theme'])) $profile->active_site_theme=(string)$payload['theme']; else $profile->active_effect=$payload['effect'] ?? $item->key; }
                if($item->category==='profile_cover') $profile->active_profile_cover=$payload['cover'] ?? $item->key;
                if($item->category==='xp_booster') { $profile->xp_boost_multiplier=(float)($payload['multiplier'] ?? 1.25); $profile->xp_boost_expires_at=now()->addHours(max(1,(int)($payload['activate_hours'] ?? 24))); }
                $profile->save();
            }
        });
        return $this->friendlyOk('تم التفعيل بنجاح', ['activated'=>true,'category'=>$item->category,'payload'=>$item->payload ?: [],'inventory_id'=>$inventory->id]);
    }
    private function friendlyOk(string $message, array $extra=[]){ if(request()->expectsJson() || request()->ajax()) return response()->json(array_merge(['ok'=>true,'message'=>$message],$extra)); return back()->with('ok',$message); }
    private function friendlyFail(string $message){ if(request()->expectsJson() || request()->ajax()) return response()->json(['ok'=>false,'message'=>$message],200); return back()->withErrors(['msg'=>$message]); }
}

