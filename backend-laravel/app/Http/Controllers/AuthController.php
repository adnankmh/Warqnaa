<?php
namespace App\Http\Controllers;

use App\Models\{User,Profile,Wallet};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Hash,RateLimiter};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\Account\AccountCancellationService;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class AuthController
{
    public function showLogin(){ return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }

    public function register(Request $r)
    {
        $data=$r->validate([
            'username'=>'required|string|min:3|max:30|alpha_dash|unique:users,username',
            'email'=>'required|email|max:190|unique:users,email',
            'password'=>'required|min:8|confirmed',
            'country_code'=>'nullable|string|size:2|not_in:IL,il',
        ]);
        $u=User::create(['username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password'])]);
        Profile::create(['user_id'=>$u->id,'display_name'=>$u->username,'country_code'=>safe_country_code($data['country_code']??'PS'),'country_name'=>country_name($data['country_code']??'PS')]);
        Wallet::create(['user_id'=>$u->id,'tokens'=>50]);
        Auth::login($u);
        return redirect()->route('games');
    }

    public function login(Request $r, AccountCancellationService $cancellation)
    {
        $cred=$r->validate(['login'=>'required|string|max:190','password'=>'required|string|max:120']);
        $key=Str::lower($cred['login']).'|'.$r->ip();
        if(RateLimiter::tooManyAttempts($key,5)){
            throw ValidationException::withMessages(['login'=>'محاولات كثيرة. حاول مرة أخرى بعد '.RateLimiter::availableIn($key).' ثانية.']);
        }
        $field=filter_var($cred['login'],FILTER_VALIDATE_EMAIL)?'email':'username';
        if(Auth::attempt([$field=>$cred['login'],'password'=>$cred['password']],false)){
            RateLimiter::clear($key);
            $user=$this->ensurePrimaryAdmin(auth()->user());
            if($user->is_banned){ Auth::logout(); return back()->withErrors(['login'=>'الحساب محظور من الإدارة']); }
            try {
                $cancellation->reactivate(auth()->user());
            } catch (GoneHttpException $e) {
                Auth::logout();
                return back()->withErrors(['login' => $e->getMessage()]);
            }
            $user->update(['last_seen_at'=>now()]);
            $r->session()->regenerate();
            return redirect()->route('games');
        }
        RateLimiter::hit($key,60);
        return back()->withErrors(['login'=>'بيانات الدخول غير صحيحة']);
    }

    private function ensurePrimaryAdmin(User $user): User
    {
        if (strcasecmp(trim((string) $user->username), 'Adnan') === 0 && !$user->is_admin) {
            $user->forceFill(['is_admin' => true])->save();
        }

        return $user->refresh();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
