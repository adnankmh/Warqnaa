<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; use App\Models\Notification;
class PageController {
 public function settings(){ return view('pages.settings'); }
 public function saveSettings(Request $r){
  $data=$r->validate(['display_name'=>'nullable|string|max:80','country_code'=>'nullable|string|max:2','font_scale'=>'nullable|numeric|min:1|max:1.8','sound_enabled'=>'nullable|boolean','active_site_theme'=>'nullable|string|in:royal,midnight,emerald,desert,galaxy,crimson,ocean,obsidian,aurora']);
  $p=auth()->user()->profile;
  if($p){
   if(!empty($data['display_name']))$p->display_name=$data['display_name'];
   if(!empty($data['country_code'])){ $p->country_code=safe_country_code($data['country_code']); $p->country_name=country_name($p->country_code); }
   $p->sound_enabled=$r->boolean('sound_enabled');
   if(!empty($data['active_site_theme'])) $p->active_site_theme=$data['active_site_theme'];
   $p->save();
  }
  return back()->with('ok','تم حفظ الإعدادات');
 }
 public function quickPreference(Request $r){
  $data=$r->validate(['theme'=>'nullable|string|in:royal,midnight,emerald,desert,galaxy,crimson,ocean,obsidian,aurora','lang'=>'nullable|string|in:ar,en,fr,tr,de,es']);
  session(['warqna_locale'=>$data['lang'] ?? session('warqna_locale','ar')]);
  $p=auth()->user()?->profile;
  if($p && !empty($data['theme'])){ $p->active_site_theme=$data['theme']; $p->save(); }
  return response()->json(['ok'=>true,'message'=>'تم حفظ التفضيلات','theme'=>$data['theme'] ?? null,'lang'=>$data['lang'] ?? null]);
 }
 public function about(){ return view('pages.about'); }
 public function contact(){ return view('pages.contact'); }
 public function sendContact(Request $r){ $data=$r->validate(['subject'=>'required|string|max:120','message'=>'required|string|max:2000']); $admin=\App\Models\User::where('is_admin',true)->first(); if($admin) Notification::create(['user_id'=>$admin->id,'type'=>'support','title'=>['ar'=>'رسالة دعم جديدة'],'body'=>['ar'=>auth()->user()->username.': '.$data['subject'].' - '.$data['message']],'url'=>route('admin')]); if($r->ajax()) return response()->json(['ok'=>true,'message'=>'تم إرسال الرسالة للدعم']); return back()->with('ok','تم إرسال الرسالة للدعم'); }
}
