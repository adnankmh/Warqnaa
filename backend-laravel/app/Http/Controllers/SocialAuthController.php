<?php

namespace App\Http\Controllers;

use App\Models\{SocialAccount,SocialAuthSession,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Hash,Http};
use Illuminate\Support\Str;
use App\Services\Account\AccountCancellationService;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class SocialAuthController extends Controller
{
    private const PROVIDERS=['google','facebook','apple'];

    public function start(Request $request,string $provider)
    {
        abort_unless(in_array($provider,self::PROVIDERS,true),404);
        $cfg=(array)config('social_auth.'.$provider,[]);
        $configured=!empty($cfg['client_id']) && !empty($cfg['client_secret']) && !empty($cfg['redirect_uri']);
        if(!$configured) return response()->json(['ok'=>false,'configured'=>false,'message'=>'لم تتم إضافة مفاتيح '.$provider.' في إعدادات الخادم بعد.'],503);
        $state=(string)Str::uuid();
        SocialAuthSession::create(['state'=>$state,'provider'=>$provider,'status'=>'pending','expires_at'=>now()->addMinutes(10)]);
        $url=$this->authorizationUrl($provider,$cfg,$state);
        return response()->json(['ok'=>true,'configured'=>true,'provider'=>$provider,'state'=>$state,'authorize_url'=>$url,'expires_in'=>600]);
    }

    public function status(string $state)
    {
        $session=SocialAuthSession::with('user.profile','user.wallet')->where('state',$state)->firstOrFail();
        if($session->expires_at->isPast() && $session->status==='pending') $session->update(['status'=>'expired','error'=>'انتهت مهلة تسجيل الدخول.']);
        if($session->status!=='completed') return response()->json(['ok'=>true,'status'=>$session->status,'error'=>$session->error]);
        $token=$session->one_time_token;
        $session->update(['status'=>'consumed','one_time_token'=>null]);
        return response()->json(['ok'=>true,'status'=>'completed','token'=>$token,'user'=>$session->user?->publicProfile(),'wallet'=>['tokens'=>(string)($session->user?->wallet?->tokens ?? 0)]]);
    }

    public function callback(Request $request,string $provider, AccountCancellationService $cancellation)
    {
        abort_unless(in_array($provider,self::PROVIDERS,true),404);
        $state=(string)$request->input('state');
        $session=SocialAuthSession::where('state',$state)->where('provider',$provider)->first();
        abort_unless($session && $session->expires_at->isFuture() && $session->status==='pending',410,'جلسة تسجيل الدخول منتهية أو مستهلكة أو غير صحيحة.');
        if($request->filled('error')){
            $session->update(['status'=>'failed','error'=>(string)$request->input('error_description',$request->input('error'))]);
            return $this->resultPage(false,'تم إلغاء تسجيل الدخول أو رفضه.');
        }
        try{
            $identity=$this->exchange($provider,(string)$request->input('code'));
            $user=DB::transaction(function()use($provider,$identity){
                $account=SocialAccount::where('provider',$provider)->where('provider_user_id',$identity['id'])->first();
                if($account) return $account->user;
                $email=$identity['email'] ?: $provider.'-'.$identity['id'].'@social.warqna.local';
                $user=User::where('email',$email)->first();
                if(!$user){
                    $base=Str::slug($identity['name'] ?: $provider.' player','_') ?: $provider.'_player';
                    $username=mb_substr($base,0,24); $n=1;
                    while(User::where('username',$username)->exists()) $username=mb_substr($base,0,20).'_'.$n++;
                    $user=User::create(['username'=>$username,'email'=>$email,'password'=>Hash::make(Str::random(48)),'email_verified_at'=>now()]);
                    $user->profile()->create(['display_name'=>$identity['name'] ?: $username,'avatar'=>'👤','avatar_data'=>$identity['avatar'],'country_code'=>'PS','country_name'=>country_name('PS'),'level'=>1,'xp'=>0]);
                    $user->wallet()->create(['tokens'=>1500]);
                }
                SocialAccount::create(['user_id'=>$user->id,'provider'=>$provider,'provider_user_id'=>$identity['id'],'email'=>$identity['email'],'display_name'=>$identity['name'],'avatar_url'=>$identity['avatar'],'meta'=>$identity['meta']]);
                return $user;
            });
            $cancellation->reactivate($user);
            $user->update(['last_seen_at'=>now()]);
            $plain=$user->createToken('social-'.$provider.'-'.now()->timestamp)->plainTextToken;
            $session->update(['status'=>'completed','user_id'=>$user->id,'one_time_token'=>$plain]);
            return $this->resultPage(true,'تم تسجيل الدخول بنجاح. ارجع إلى تطبيق ورقنا وسيُفتح الحساب تلقائيًا.');
        }catch(GoneHttpException $e){
            $session->update(['status'=>'failed','error'=>$e->getMessage()]);
            return $this->resultPage(false,$e->getMessage());
        }catch(\Throwable $e){
            report($e);
            $session->update(['status'=>'failed','error'=>'تعذر التحقق من حساب '.$provider.'. راجع مفاتيح OAuth وعنوان Callback.']);
            return $this->resultPage(false,'تعذر إكمال تسجيل الدخول. راجع إعدادات مزود الخدمة ثم حاول مجددًا.');
        }
    }

    private function authorizationUrl(string $provider,array $cfg,string $state): string
    {
        $common=['client_id'=>$cfg['client_id'],'redirect_uri'=>$cfg['redirect_uri'],'state'=>$state,'response_type'=>'code'];
        if($provider==='google') return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($common+['scope'=>'openid email profile','prompt'=>'select_account']);
        if($provider==='facebook'){ $version=(string)($cfg['graph_version'] ?? 'v22.0'); return 'https://www.facebook.com/'.$version.'/dialog/oauth?'.http_build_query($common+['scope'=>'email,public_profile']); }
        return 'https://appleid.apple.com/auth/authorize?'.http_build_query($common+['scope'=>'name email','response_mode'=>'form_post']);
    }

    private function exchange(string $provider,string $code): array
    {
        abort_if($code==='',422,'رمز OAuth مفقود.');
        $cfg=(array)config('social_auth.'.$provider,[]);
        if($provider==='google'){
            $token=Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token',['code'=>$code,'client_id'=>$cfg['client_id'],'client_secret'=>$cfg['client_secret'],'redirect_uri'=>$cfg['redirect_uri'],'grant_type'=>'authorization_code'])->throw()->json();
            $me=Http::withToken($token['access_token'])->timeout(20)->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
            return ['id'=>(string)$me['sub'],'email'=>$me['email']??null,'name'=>$me['name']??null,'avatar'=>$me['picture']??null,'meta'=>['email_verified'=>$me['email_verified']??false]];
        }
        if($provider==='facebook'){
            $version=(string)($cfg['graph_version'] ?? 'v22.0');
            $token=Http::asForm()->timeout(20)->post('https://graph.facebook.com/'.$version.'/oauth/access_token',['code'=>$code,'client_id'=>$cfg['client_id'],'client_secret'=>$cfg['client_secret'],'redirect_uri'=>$cfg['redirect_uri']])->throw()->json();
            $me=Http::withToken($token['access_token'])->timeout(20)->get('https://graph.facebook.com/me',['fields'=>'id,name,email,picture.type(large)'])->throw()->json();
            return ['id'=>(string)$me['id'],'email'=>$me['email']??null,'name'=>$me['name']??null,'avatar'=>$me['picture']['data']['url']??null,'meta'=>[]];
        }
        $token=Http::asForm()->timeout(20)->post('https://appleid.apple.com/auth/token',['code'=>$code,'client_id'=>$cfg['client_id'],'client_secret'=>$cfg['client_secret'],'redirect_uri'=>$cfg['redirect_uri'],'grant_type'=>'authorization_code'])->throw()->json();
        $parts=explode('.',(string)($token['id_token']??''));
        abort_unless(count($parts)===3,422,'Apple identity token is invalid.');
        $payload=json_decode(base64_decode(strtr($parts[1],'-_','+/').str_repeat('=',(4-strlen($parts[1])%4)%4)),true) ?: [];
        abort_unless(($payload['iss'] ?? null)==='https://appleid.apple.com',422,'Apple issuer is invalid.');
        $aud=$payload['aud'] ?? null;
        abort_unless($aud===$cfg['client_id'] || (is_array($aud) && in_array($cfg['client_id'],$aud,true)),422,'Apple audience is invalid.');
        abort_unless((int)($payload['exp'] ?? 0)>time(),422,'Apple identity token expired.');
        abort_unless(!empty($payload['sub']),422,'Apple subject is missing.');
        return ['id'=>(string)$payload['sub'],'email'=>$payload['email']??null,'name'=>'Apple Player','avatar'=>null,'meta'=>['private_email'=>$payload['is_private_email']??false,'issuer_verified'=>true,'audience_verified'=>true]];
    }

    private function resultPage(bool $ok,string $message)
    {
        $color=$ok?'#22c55e':'#ef4444';
        return response('<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="background:#050b14;color:white;font-family:Arial;display:grid;place-items:center;min-height:100vh"><main style="max-width:520px;text-align:center;padding:32px;border:1px solid #ffffff22;border-radius:24px;background:#111827"><div style="font-size:64px">'.($ok?'✅':'⚠️').'</div><h1 style="color:'.$color.'">ورقنا</h1><p style="line-height:1.8">'.e($message).'</p></main></body></html>');
    }
}
