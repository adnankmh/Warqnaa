<?php

namespace App\Http\Controllers;

use App\Models\{Friendship,Message,Notification,User};
use App\Services\Wallet\WalletService;
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

    public function request(Request $request, User $user)
    {
        $me = $request->user();
        abort_if($me->id === $user->id, 422, 'لا يمكنك إرسال طلب لنفسك');
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
        return response()->json(['ok' => true, 'message' => 'تم إرسال طلب الصداقة', 'friendship' => $friendship], 201);
    }

    public function respond(Request $request, Friendship $friendship)
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

    public function send(Request $request, User $user)
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
        return response()->json(['ok' => true, 'message' => ['id' => $message->id, 'mine' => true, 'body' => $message->body, 'time' => $message->created_at?->format('H:i')]], 201);
    }

    public function transfer(Request $request, WalletService $wallet)
    {
        $data = $request->validate(['receiver' => 'required|string|max:120', 'amount' => 'required|integer|min:1|max:1000000000000']);
        $sender = $request->user();
        $receiver = User::where('username', $data['receiver'])->orWhere('email', $data['receiver'])->first();
        abort_unless($receiver, 404, 'لم يتم العثور على المستلم');
        abort_if($receiver->id === $sender->id, 422, 'لا يمكنك التحويل لنفسك');
        $fee = (int) ceil(((int) $data['amount']) * 0.10);
        $total = (int) $data['amount'] + $fee;

        try {
            DB::transaction(function () use ($wallet, $sender, $receiver, $data, $fee) {
                $wallet->debit($sender, (int) $data['amount'] + $fee, 'transfer_sent', ['to' => $receiver->id, 'fee_percent' => 10, 'fee' => $fee]);
                $wallet->credit($receiver, (int) $data['amount'], 'transfer_received', ['from' => $sender->id]);
                $admin = User::where('username', 'Adnan')->where('is_admin', true)->first() ?: User::where('is_admin', true)->first();
                if ($admin && $fee > 0) {
                    $wallet->credit($admin, $fee, 'transfer_fee', ['from' => $sender->id, 'to' => $receiver->id, 'fee_percent' => 10]);
                }
            });
        } catch (\Throwable) {
            return response()->json(['ok' => false, 'message' => 'الرصيد غير كافٍ. المطلوب مع عمولة الإدارة: ' . number_format($total)], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'تم إرسال ' . number_format((int) $data['amount']) . ' توكنز، وخصم عمولة إدارة 10% بقيمة ' . number_format($fee) . '.',
            'amount' => (int) $data['amount'],
            'fee' => $fee,
            'total_debited' => $total,
            'wallet' => $sender->wallet()->fresh(),
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
        return [
            'id' => $user->id,
            'username' => $user->username,
            'display_name' => $profile?->display_name ?: $user->username,
            'avatar' => $profile?->avatar,
            'level' => (int) ($profile?->level ?? 1),
            'country_code' => $profile?->country_code ?: 'PS',
            'name_color' => $profile?->name_color ?: '#facc15',
            'badge' => $profile?->badge,
            'online' => $user->last_seen_at?->gt(now()->subMinutes(3)) ?? false,
        ];
    }

    private function cleanChat(string $body): string
    {
        $bad = ['كلب','حمار','حقير','قذر','غبي','وسخ','خرا','شرموط','قحبة','fuck','shit','bitch','asshole'];
        $body = trim(strip_tags($body));
        foreach ($bad as $word) $body = preg_replace('/' . preg_quote($word, '/') . '/iu', '***', $body);
        return mb_substr($body, 0, 1000);
    }
}
