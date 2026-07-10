<?php
namespace App\Http\Controllers;

use App\Models\{EconomySeason,StoreOffer,RareCollectible};
use Illuminate\Http\Request;

class EconomyAdminController
{
 private function guard(){ abort_unless(auth()->user()?->is_admin,403); }

 public function saveSeason(Request $r)
 {
  $this->guard();
  $d=$r->validate(['key'=>'required|string|max:80','name_ar'=>'required|string|max:120','active'=>'nullable|boolean','starts_at'=>'nullable|date','ends_at'=>'nullable|date']);
  EconomySeason::updateOrCreate(['key'=>$d['key']],[
   'name'=>['ar'=>$d['name_ar'],'en'=>$d['key']],
   'starts_at'=>$d['starts_at'] ?? null,'ends_at'=>$d['ends_at'] ?? null,'active'=>$r->boolean('active'),
   'rewards'=>['daily'=>'توكنز يومية','top'=>'جوائز صدارة']
  ]);
  return back()->with('ok','تم حفظ الموسم');
 }

 public function saveOffer(Request $r)
 {
  $this->guard();
  $d=$r->validate(['key'=>'required|string|max:80','title_ar'=>'required|string|max:120','discount_percent'=>'required|integer|min:0|max:95','active'=>'nullable|boolean']);
  StoreOffer::updateOrCreate(['key'=>$d['key']],[
   'title'=>['ar'=>$d['title_ar'],'en'=>$d['key']],
   'description'=>['ar'=>'عرض مميز داخل المتجر'],
   'discount_percent'=>$d['discount_percent'],'active'=>$r->boolean('active'),'item_keys'=>[]
  ]);
  return back()->with('ok','تم حفظ العرض');
 }

 public function saveRare(Request $r)
 {
  $this->guard();
  $d=$r->validate(['key'=>'required|string|max:80','name_ar'=>'required|string|max:120','rarity'=>'required|string|max:40','supply'=>'nullable|integer|min:1','active'=>'nullable|boolean']);
  RareCollectible::updateOrCreate(['key'=>$d['key']],[
   'name'=>['ar'=>$d['name_ar'],'en'=>$d['key']],
   'rarity'=>$d['rarity'],'supply'=>$d['supply'] ?? null,'active'=>$r->boolean('active'),
   'payload'=>['icon'=>'💎','source'=>'admin']
  ]);
  return back()->with('ok','تم حفظ المقتنى النادر');
 }
}
