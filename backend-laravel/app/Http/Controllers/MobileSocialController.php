<?php

namespace App\Http\Controllers;

use App\Models\{Friendship,Message,Notification,Room,User};
use App\Services\Wallet\WalletService;
use App\Services\Platform\ProductionConfigService;
use App\Services\Notifications\FirebasePushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileSocialController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $relations = Friendship::with(['requester.profile', 'addressee.profile'])
            ->where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('addressee_id', $user->id))
            ->latest()->get();

        return response()->json([
            'ok' => true,
            'accepted' => $relations->where('status', 'accepted')->map(fn ($f) => $this->relationPayload($f, $user->id))->values(),
            'incoming' => $relations->where('status', 'pending')->where('addressee_id', $user->id)->map(fn ($f) => $this->relationPayload($f, $user->id))->values(),
            'outgoing' => $relations->where('status', 'pending')->where('requester_id', $user->id)->map(fn ($f) => $this->relationPayload($f, $user->id))->values(),
            'blocked' => $relations->where('status', 'blocked')->map(fn ($f) => $this->relationPayload($f, $user->id))->values(),
        ]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $users = User::with('profile')
            ->where('id', '!=', $request->user()->id)
            ->when($query !== '', fn ($q) => $q->where(function ($q) use ($query) {
                $q->where('username', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%')
                    ->orWhereHas('profile', fn ($p) => $p->where('display_name', 'like', '%' . $query . '%'));
            }))
            ->limit(30)->get();

        return response()->json(['ok' => true, 'users' => $users->map(fn ($u) => $this->userPayload($u))]);
    }

    public function profile(Request $request, User $user)
    {
        $this->assertNotBlocked($request->user()->id, $user->id);
        return response()->json(['ok' => true, 'user' => $this->userPayload($user->load('profile'))]);
    }

    public function inviteToRoom(Request $request, User $user, FirebasePushService $push)
    {
        $this->assertFriends($request->user()->id, $user->id);
        $data = $request->validate(['room_code' => 'required|string|max:20']);
        $room = Room::where('code', strtoupper($data['room_code']))->whereIn('status', ['waiting','bidding','playing'])->firstOrFail();
        abort_unless((int)$room->owner_id === (int)$request->user()->id || $room->players()->where('user_id', $request->user()->id)->exists(), 403, 'يجب أن تكون داخل الغرفة لإرسال الدعوة.');
        Notification::create(['user_id'=>$user->id,'type'=>'room_invite','title'=>['ar'=>'دعوة لعبة','en'=>'Game invitation'],'body'=>['ar'=>$request->user()->username.' دعاك إلى غرفة '.$room->code,'en'=>$request->user()->username.' invited you to room '.$room->code],'meta'=>['room_code'=>$room->code,'from'=>$request->user()->id]]);
        $push->sendToUser($user, 'دعوة لعبة', $request->user()->username.' دعاك إلى غرفة '.$room->code, ['route'=>'room:'.$room->code,'type'=>'room_invite','room_code'=>$room->code,'sender_id'=>$request->user()->id]);
        return response()->json(['ok'=>true,'message'=>'تم إرسال الدعوة حتى لو كان اللاعب خارج التطبيق.']);
    }

    public function inviteAllToRoom(Request $request, FirebasePushService $push)
    {
        $data = $request->validate(['room_code' => 'required|string|max:20']);
        $room = Room::where('code', strtoupper($data['room_code']))->whereIn('status', ['waiting','bidding','playing'])->firstOrFail();
        abort_unless((int)$room->owner_id === (int)$request->user()->id || $room->players()->where('user_id', $request->user()->id)->exists(), 403);
        $relations = Friendship::where('status','accepted')->where(fn($q)=>$q->where('requester_id',$request->user()->id)->orWhere('addressee_id',$request->user()->id))->get();
        $sent=0;
        foreach($relations as $relation){
            $id=(int)$relation->requester_id===(int)$request->user()->id ? (int)$relation->addressee_id : (int)$relation->requester_id;
            $friend=User::find($id); if(!$friend) continue;
            $push->sendToUser($friend, 'دعوة جماعية للعبة', $request->user()->username.' دعاك إلى غرفة '.$room->code, ['route'=>'room:'.$room->code,'type'=>'room_invite','room_code'=>$room->code,'sender_id'=>$request->user()->id]);
            Notification::create(['user_id'=>$friend->id,'type'=>'room_invite','title'=>['ar'=>'دعوة لعبة','en'=>'Game invitation'],'body'=>['ar'=>$request->user()->username.' دعاك إلى غرفة '.$room->code],'meta'=>['room_code'=>$room->code,'from'=>$request->user()->id]]); $sent++;
        }
        return response()->json(['ok'=>true,'message'=>'تم إرسال الدعوة إلى '.$sent.' صديق.','sent'=>$sent]);
    }

    public function request(Request $request, User $user, FirebasePushService $push)
    {
        $me = $request->user();
        abort_if($me->id === $user->id, 422, 'لا يمكنك إرسال طلب لنفسك');
        $this->assertNotBlocked($me->id, $user->id);
        $existing = $this->relation($me->id, $user->id);
        if ($existing) {
            return response()->json(['ok' => false, 'message' => 'توجد علاقة أو دعوة سابقة مع هذا اللاعب', 'status' => $existing->status], 409);
        }
        $friendship = Friendship::create(['requester_id' => $me->id, 'addressee_id' => $user->id, 'status' => 'pending']);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'friend_request',
            'title' => ['ar' => 'طلب صداقة', 'en' => 'Friend request'],
            'body' => ['ar' => $me->username . ' أرسل لك طلب صداقة', 'en' => $me->username . ' sent you a friend request'],
            'meta' => ['friendship_id' => $friendship->id, 'from' => $me->id],
        ]);
        $push->sendToUser($user, 'طلب صداقة جديد', $me->username.' أرسل لك طلب صداقة.', [
            'route' => 'friends',
            'type' => 'friend_request',
            'friendship_id' => $friendship->id,
            'sender_id' => $me->id,
        ]);
        return response()->json(['ok' => true, 'message' => 'تم إرسال طلب الصداقة', 'friendship' => $friendship], 201);
    }

    public function respond(Request $request, Friendship $friendship, FirebasePushService $push)
    {
        abort_unless($friendship->addressee_id === $request->user()->id && $friendship->status === 'pending', 403);
        $data = $request->validate(['status' => 'required|in:accepted,rejected']);
        if ($data['status'] === 'accepted') {
            $friendship->update(['status' => 'accepted']);
        } else {
            $friendship->delete();
        }
        Notification::create([
            'user_id' => $friendship->requester_id,
            'type' => 'friend_response',
            'title' => ['ar' => 'رد على طلب الصداقة', 'en' => 'Friend request response'],
            'body' => ['ar' => $request->user()->username . ($data['status'] === 'accepted' ? ' قبل طلب الصداقة' : ' رفض طلب الصداقة')],
        ]);
        $requester = User::find($friendship->requester_id);
        if ($requester) {
            $resultText = $data['status'] === 'accepted' ? 'قبل طلب صداقتك.' : 'رفض طلب صداقتك.';
            $push->sendToUser($requester, 'رد على طلب الصداقة', $request->user()->username.' '.$resultText, [
                'route' => 'friends',
                'type' => 'friend_response',
                'sender_id' => $request->user()->id,
                'status' => $data['status'],
            ]);
        }
        return response()->json(['ok' => true, 'message' => $data['status'] === 'accepted' ? 'تم قبول الطلب' : 'تم رفض الطلب']);
    }

    public function cancel(Request $request, Friendship $friendship)
    {
        abort_unless($friendship->requester_id === $request->user()->id && $friendship->status === 'pending', 403);
        $friendship->delete();
        return response()->json(['ok' => true, 'message' => 'تم إلغاء الطلب']);
    }

    public function block(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422);
        $existing = $this->relation($request->user()->id, $user->id);
        if ($existing) {
            $existing->update(['requester_id' => $request->user()->id, 'addressee_id' => $user->id, 'status' => 'blocked']);
        } else {
            Friendship::create(['requester_id' => $request->user()->id, 'addressee_id' => $user->id, 'status' => 'blocked']);
        }
        return response()->json(['ok' => true, 'message' => 'تم حظر اللاعب']);
    }

    public function unblock(Request $request, User $user)
    {
        $existing = $this->relation($request->user()->id, $user->id);
        abort_unless($existing && $existing->status === 'blocked' && $existing->requester_id === $request->user()->id, 403);
        $existing->delete();
        return response()->json(['ok' => true, 'message' => 'تم إلغاء الحظر']);
    }

    public function thread(Request $request, User $user)
    {
        $this->assertFriends($request->user()->id, $user->id);
        $messages = Message::with('sender.profile')->where(function ($q) use ($request, $user) {
            $q->where('sender_id', $request->user()->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($request, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $request->user()->id);
        })->latest()->limit(100)->get()->reverse()->values();
        Message::where('sender_id', $user->id)->where('receiver_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json([
            'ok' => true,
            'friend' => $this->userPayload($user->load('profile')),
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'mine' => $m->sender_id === $request->user()->id,
                'name' => $m->sender?->profile?->display_name ?: $m->sender?->username,
                'body' => $m->body,
                'color' => $m->sender?->profile?->chat_color ?: '#ffffff',
                'time' => $m->created_at?->format('H:i'),
                'read' => (bool) $m->read_at,
            ]),
        ]);
    }

    public function send(Request $request, User $user, FirebasePushService $push)
    {
        $this->assertFriends($request->user()->id, $user->id);
        $data = $request->validate(['body' => 'required|string|max:1000']);
        $body = $this->cleanChat($data['body']);
        abort_if($body === '', 422, 'الرسالة فارغة');
        $message = Message::create(['sender_id' => $request->user()->id, 'receiver_id' => $user->id, 'body' => $body]);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'private_message',
            'title' => ['ar' => 'رسالة جديدة', 'en' => 'New message'],
            'body' => ['ar' => $request->user()->username . ' أرسل لك رسالة'],
            'meta' => ['sender_id' => $request->user()->id],
        ]);
        $preview = mb_strlen($body) > 100 ? mb_substr($body, 0, 97).'…' : $body;
        $push->sendToUser($user, $request->user()->username, $preview, [
            'route' => 'friend-chat:'.$request->user()->id,
            'type' => 'private_message',
            'sender_id' => $request->user()->id,
            'message_id' => $message->id,
        ]);
        return response()->json(['ok' => true, 'message' => ['id' => $message->id, 'mine' => true, 'body' => $message->body, 'time' => $message->created_at?->format('H:i')]], 201);
    }

    public function transfer(Request $request, WalletService $wallet, ProductionConfigService $productionConfig)
    {
        abort_unless($productionConfig->enabled('token_transfers', true), 503, 'تحويل التوكنز متوقف مؤقتًا.');
        $data = $request->validate(['receiver' => 'required|string|max:120', 'amount' => 'required|integer|min:10|max:1000000000000']);
        $sender = $request->user();
        $receiver = User::where('username', $data['receiver'])->orWhere('email', $data['receiver'])->first();
        abort_unless($receiver, 404, 'لم يتم العثور على المستلم');
        abort_if($receiver->id === $sender->id, 422, 'لا يمكنك التحويل لنفسك');
        $feePercent = max(0, min(100, (int) data_get($productionConfig->flags(), 'token_transfers.payload.fee_percent', config('warqna.token_transfer_fee_percent', 10))));
        $fee = (int) ceil(((int) $data['amount']) * ($feePercent / 100));
        $total = (int) $data['amount'] + $fee;

        try {
            DB::transaction(function () use ($wallet, $sender, $receiver, $data, $fee, $feePercent) {
                $wallet->debit($sender, (int) $data['amount'] + $fee, 'transfer_sent', ['to' => $receiver->id, 'fee_percent' => $feePercent, 'fee' => $fee]);
                $wallet->credit($receiver, (int) $data['amount'], 'transfer_received', ['from' => $sender->id]);
                $admin = User::where('username', 'Adnan')->where('is_admin', true)->first() ?: User::where('is_admin', true)->first();
                if ($admin && $fee > 0) {
                    $wallet->credit($admin, $fee, 'transfer_fee', ['from' => $sender->id, 'to' => $receiver->id, 'fee_percent' => $feePercent]);
                }
            });
        } catch (\Throwable) {
            return response()->json(['ok' => false, 'message' => 'الرصيد غير كافٍ. المطلوب مع رسوم التحويل: ' . number_format($total)], 422);
        }

        $freshWallet = $sender->wallet()->firstOrFail();

        return response()->json([
            'ok' => true,
            'message' => 'تم إرسال ' . number_format((int) $data['amount']) . ' توكنز، وخصم رسوم تحويل ' . $feePercent . '% بقيمة ' . number_format($fee) . '.',
            'amount' => (int) $data['amount'],
            'fee' => $fee,
            'total_debited' => $total,
            'wallet' => $freshWallet,
        ]);
    }

    private function relation(int $a, int $b): ?Friendship
    {
        return Friendship::where(fn ($q) => $q->where('requester_id', $a)->where('addressee_id', $b))
            ->orWhere(fn ($q) => $q->where('requester_id', $b)->where('addressee_id', $a))->first();
    }

    private function assertFriends(int $a, int $b): void
    {
        abort_unless(Friendship::where('status', 'accepted')->where(function ($q) use ($a, $b) {
            $q->where(fn ($q) => $q->where('requester_id', $a)->where('addressee_id', $b))
                ->orWhere(fn ($q) => $q->where('requester_id', $b)->where('addressee_id', $a));
        })->exists(), 403, 'الدردشة الخاصة متاحة للأصدقاء فقط');
    }

    /** @return array<string,mixed> */
    private function relationPayload(Friendship $friendship, int $me): array
    {
        $other = $friendship->requester_id === $me ? $friendship->addressee : $friendship->requester;
        return ['id' => $friendship->id, 'status' => $friendship->status, 'mine' => $friendship->requester_id === $me, 'user' => $this->userPayload($other)];
    }

    /** @return array<string,mixed> */
    private function userPayload(?User $user): array
    {
        if (!$user) return [];
        $profile = $user->profile;
        $membership = $user->clubMembership()->with('club')->first();
        return [
            'id' => $user->id,
            'username' => $user->username,
            'display_name' => $profile?->display_name ?: $user->username,
            'avatar' => $profile?->avatar,
            'level' => (int) ($profile?->level ?? 1),
            'country_code' => $profile?->country_code ?: 'PS',
            'country_name' => $profile?->country_name ?: country_name($profile?->country_code ?: 'PS'),
            'flag' => (string)(config('countries.'.safe_country_code($profile?->country_code ?: 'PS').'.flag') ?? '🇵🇸'),
            'name_color' => $profile?->name_color ?: '#facc15',
            'badge' => $profile?->badge,
            'pasha_days' => (int) ($profile?->pasha_days ?? 0),
            'games_played' => (int) ($profile?->games_played ?? 0),
            'wins' => (int) ($profile?->wins ?? 0),
            'round_points' => (int) ($profile?->round_points ?? 0),
            'tournament_points' => (int) ($profile?->tournament_points ?? 0),
            'club_points' => (int) ($profile?->club_points ?? 0),
            'club' => $membership?->club ? [
                'id' => $membership->club->id,
                'name' => $membership->club->name,
                'logo' => $membership->club->logo,
                'level' => (int) $membership->club->level,
                'role' => $membership->role,
            ] : null,
            'online' => $user->last_seen_at?->gt(now()->subMinutes(3)) ?? false,
        ];
    }

    private function assertNotBlocked(int $a, int $b): void
    {
        $blocked = Friendship::where('status','blocked')->where(function ($q) use ($a,$b) {
            $q->where(fn($q)=>$q->where('requester_id',$a)->where('addressee_id',$b))
              ->orWhere(fn($q)=>$q->where('requester_id',$b)->where('addressee_id',$a));
        })->exists();
        abort_if($blocked, 403, 'لا يمكن التواصل أو اللعب مع لاعب موجود في قائمة الحظر.');
    }

    private function cleanChat(string $body): string
    {
        $bad = ['كلب','حمار','حقير','قذر','غبي','وسخ','خرا','شرموط','قحبة','fuck','shit','bitch','asshole'];
        $body = trim(strip_tags($body));
        foreach ($bad as $word) $body = preg_replace('/' . preg_quote($word, '/') . '/iu', '***', $body);
        return mb_substr($body, 0, 1000);
    }
}
