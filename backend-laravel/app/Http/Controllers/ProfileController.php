<?php
namespace App\Http\Controllers;

use App\Models\{User,Friendship};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController
{
    public function show(?User $user=null)
    {
        if(!auth()->check()){
            if(request()->ajax() || request()->expectsJson() || request()->headers->get('X-Requested-With')) return response('<div class="profile-auth-required">يجب تسجيل الدخول أولًا لفتح البروفايل. <a href="'.route('login').'">تسجيل الدخول</a></div>',401);
            return redirect()->route('login')->withErrors(['msg'=>'يجب تسجيل الدخول أولًا.']);
        }
        $user=$user?:auth()->user();
        $user->load('profile','wallet','inventoryItems.storeItem');
        $friends=collect();
        if($user->id===auth()->id()){
            $ids=Friendship::where('status','accepted')->where(function($q){$q->where('requester_id',auth()->id())->orWhere('addressee_id',auth()->id());})->get()->map(fn($f)=>$f->requester_id===auth()->id()?$f->addressee_id:$f->requester_id);
            $friends=User::with('profile')->whereIn('id',$ids)->orderBy('username')->get();
        }
        $relation=null;
        if($user->id!==auth()->id()){
            $relation=Friendship::where(function($q) use($user){$q->where('requester_id',auth()->id())->where('addressee_id',$user->id);})
                ->orWhere(function($q) use($user){$q->where('requester_id',$user->id)->where('addressee_id',auth()->id());})
                ->latest()->first();
        }
        if(request()->ajax() || request()->headers->get('X-Requested-With')) return view('profile.modal',compact('user','friends','relation'));
        return view('profile.show',compact('user','friends','relation'));
    }


    public function update(Request $request)
    {
        $user=auth()->user();
        $data=$request->validate([
            'display_name'=>'nullable|string|max:80',
            'country_code'=>'nullable|string|size:2|not_in:IL,il',
            'favorite_game_key'=>'nullable|string|exists:games,key',
            'email'=>'nullable|email|max:190|unique:users,email,'.$user->id,
            'password'=>'nullable|string|min:6|confirmed',
            'avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if(!empty($data['email'])) $user->email=$data['email'];
        if(!empty($data['password'])) $user->password=Hash::make($data['password']);
        $user->save();
        $profile=$user->profile;
        if($profile){
            if(isset($data['display_name'])) $profile->display_name=$data['display_name'] ?: $user->username;
            if(!empty($data['country_code'])){
                $profile->country_code=safe_country_code($data['country_code']);
                $profile->country_name=country_name($profile->country_code);
            }
            if(!empty($data['favorite_game_key'])) $profile->favorite_game_key=$data['favorite_game_key'];
            if($request->hasFile('avatar')){
                $dir=public_path('uploads/avatars'); if(!is_dir($dir)) mkdir($dir,0775,true);
                $file=$request->file('avatar');
                $name='avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($dir,$name);
                $profile->avatar='/uploads/avatars/'.$name;
            }
            $profile->save();
        }
        if($request->expectsJson() || $request->ajax()){
            $user->load('profile');
            return response()->json(['ok'=>true,'message'=>'تم تحديث البروفايل بنجاح','profile'=>[
                'country_code'=>$user->profile?->country_code,
                'country_name'=>country_name($user->profile?->country_code ?? 'PS'),
                'flag_url'=>flag_url($user->profile?->country_code ?? 'PS'),
                'avatar'=>$user->profile?->avatar ?: '/assets/avatars/default.svg',
                'display_name'=>$user->profile?->display_name ?: $user->username,
            ]]);
        }
        return back()->with('ok','تم تحديث البروفايل بنجاح');
    }

    public function search(Request $request)
    {
        if(!auth()->user()?->is_admin){
            return redirect()->route('friends')->with('ok','بحث اللاعبين متاح للاعبين داخل دردشة الأصدقاء فقط.');
        }
        $q=mb_substr(trim((string)$request->query('q','')),0,60);
        $users=collect();
        if($q !== ''){
            $users=User::with('profile','wallet')
                ->where(function($w) use($q){
                    $w->where('username','like','%'.$q.'%')
                      ->orWhere('email','like','%'.$q.'%')
                      ->orWhereHas('profile',fn($p)=>$p->where('display_name','like','%'.$q.'%'));
                })
                ->orderByRaw('CASE WHEN username LIKE ? THEN 0 ELSE 1 END',[$q.'%'])
                ->limit(30)->get();
        }
        return view('profile.search',compact('q','users'));
    }
}
