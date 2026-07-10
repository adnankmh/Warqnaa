<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController
{
    public function index()
    {
        $user=auth()->user()->load('wallet','walletTransactions');
        $transactions=$user->walletTransactions()->latest()->limit(200)->get();
        return view('tokens.index',compact('user','transactions'));
    }

    public function transfer(Request $r, WalletService $wallet)
    {
        $data=$r->validate(['receiver'=>'required|string','amount'=>'required|integer|min:1|max:100000000']);
        $to=User::where('username',$data['receiver'])->orWhere('email',$data['receiver'])->first();
        if(!$to) return $this->fail('لم يتم العثور على اللاعب المستلم.');
        if($to->id===auth()->id()) return $this->fail('لا يمكنك إرسال التوكنز لنفسك.');
        $fee=(int)ceil($data['amount']*.10);
        try{
            DB::transaction(function() use($wallet,$to,$data,$fee){
                $wallet->debit(auth()->user(),$data['amount']+$fee,'transfer_sent',['to'=>$to->id,'fee'=>$fee]);
                $wallet->credit($to,$data['amount'],'transfer_received',['from'=>auth()->id()]);
                $admin=User::where('is_admin',true)->first();
                if($admin && $fee>0) $wallet->credit($admin,$fee,'transfer_fee',['from'=>auth()->id(),'to'=>$to->id]);
            });
        }catch(\Throwable $e){ return $this->fail('رصيدك غير كافٍ. المبلغ المطلوب مع العمولة: '.number_format($data['amount']+$fee).' توكنز.'); }
        return $this->ok('تم إرسال '.number_format($data['amount']).' توكنز بنجاح. تم خصم عمولة 10%.');
    }
    private function ok(string $m){ if(request()->expectsJson()||request()->ajax()) return response()->json(['ok'=>true,'message'=>$m]); return back()->with('ok',$m); }
    private function fail(string $m){ if(request()->expectsJson()||request()->ajax()) return response()->json(['ok'=>false,'message'=>$m],200); return back()->withErrors(['msg'=>$m]); }
}
