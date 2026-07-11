<?php

namespace App\Http\Controllers;

use App\Models\{Room, RoomPlayer, VoiceSignal};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Platform\ProductionConfigService;

class MobileVoiceController extends Controller
{
    public function __construct(private readonly ProductionConfigService $productionConfig) {}

    public function join(Request $request, Room $room)
    {
        $player = $this->player($request, $room);
        $this->assertVoiceRoom($room);

        $player->update([
            'voice_joined_at' => now(),
            'voice_last_seen_at' => now(),
            'voice_muted' => false,
            'voice_deafened' => false,
        ]);

        return response()->json([
            'ok' => true,
            'self_id' => $request->user()->id,
            'room_code' => $room->code,
            'ice_servers' => $this->iceServers(),
            'voice_diagnostics' => [
                'turn_configured' => count((array)config('voice.turn_urls',[])) > 0,
                'secure_required' => true,
                'poll_interval_ms' => 900,
            ],
            'participants' => $this->participants($room, $request->user()->id),
            'message' => 'تم الانضمام إلى المحادثة الصوتية.',
        ]);
    }

    public function poll(Request $request, Room $room)
    {
        $player = $this->player($request, $room);
        $this->assertVoiceRoom($room);
        $player->update(['voice_last_seen_at' => now()]);

        $signals = DB::transaction(function () use ($room, $request) {
            $rows = VoiceSignal::query()
                ->where('room_id', $room->id)
                ->whereNull('delivered_at')
                ->where('sender_id', '!=', $request->user()->id)
                ->where(function ($query) use ($request) {
                    $query->whereNull('recipient_id')->orWhere('recipient_id', $request->user()->id);
                })
                ->orderBy('id')
                ->limit(100)
                ->lockForUpdate()
                ->get();

            VoiceSignal::whereKey($rows->modelKeys())->update(['delivered_at' => now()]);

            return $rows->map(fn (VoiceSignal $signal) => [
                'id' => $signal->id,
                'sender_id' => $signal->sender_id,
                'recipient_id' => $signal->recipient_id,
                'type' => $signal->signal_type,
                'payload' => $signal->payload,
                'created_at' => $signal->created_at?->toIso8601String(),
            ])->values();
        });

        VoiceSignal::query()
            ->where('room_id', $room->id)
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<', now()->subMinutes(5))
            ->delete();

        return response()->json([
            'ok' => true,
            'participants' => $this->participants($room, $request->user()->id),
            'signals' => $signals,
        ]);
    }

    public function signal(Request $request, Room $room)
    {
        $this->player($request, $room);
        $this->assertVoiceRoom($room);
        $data = $request->validate([
            'recipient_id' => 'required|integer',
            'type' => 'required|in:offer,answer,candidate,renegotiate',
            'payload' => 'required|array',
        ]);
        abort_if((int) $data['recipient_id'] === (int) $request->user()->id, 422, 'لا يمكن إرسال إشارة صوتية إلى الحساب نفسه.');
        abort_if(strlen(json_encode($data['payload'], JSON_UNESCAPED_UNICODE)) > 65535, 413, 'بيانات الإشارة الصوتية أكبر من الحد المسموح.');

        $recipientExists = $room->players()
            ->where('user_id', $data['recipient_id'])
            ->where('is_bot', false)
            ->exists();
        abort_unless($recipientExists, 422, 'اللاعب المستلم ليس داخل الغرفة.');

        $signal = VoiceSignal::create([
            'room_id' => $room->id,
            'sender_id' => $request->user()->id,
            'recipient_id' => $data['recipient_id'],
            'signal_type' => $data['type'],
            'payload' => $data['payload'],
        ]);

        return response()->json(['ok' => true, 'signal_id' => $signal->id], 201);
    }

    public function controls(Request $request, Room $room)
    {
        $player = $this->player($request, $room);
        $this->assertVoiceRoom($room);
        $data = $request->validate([
            'muted' => 'required|boolean',
            'deafened' => 'required|boolean',
        ]);
        $player->update([
            'voice_muted' => $data['muted'],
            'voice_deafened' => $data['deafened'],
            'voice_last_seen_at' => now(),
        ]);
        return response()->json(['ok' => true]);
    }

    public function leave(Request $request, Room $room)
    {
        $player = $this->player($request, $room);
        $player->update([
            'voice_joined_at' => null,
            'voice_last_seen_at' => null,
            'voice_muted' => true,
            'voice_deafened' => true,
        ]);
        VoiceSignal::query()
            ->where('room_id', $room->id)
            ->where(function ($query) use ($request) {
                $query->where('sender_id', $request->user()->id)
                    ->orWhere('recipient_id', $request->user()->id);
            })
            ->delete();
        return response()->json(['ok' => true, 'message' => 'تمت مغادرة الصوت.']);
    }

    private function player(Request $request, Room $room): RoomPlayer
    {
        $player = $room->players()
            ->where('user_id', $request->user()->id)
            ->where('is_bot', false)
            ->first();
        abort_unless($player, 403, 'أنت لست داخل هذه الغرفة.');
        return $player;
    }

    private function assertVoiceRoom(Room $room): void
    {
        abort_unless($this->productionConfig->enabled('voice_rooms', true), 503, 'الغرف الصوتية متوقفة مؤقتًا.');
        $state = $room->state ?: [];
        abort_unless(!empty($state['voice_enabled']) || !empty($state['voice_room']), 422, 'هذه غرفة عادية وليست صوتية.');
        abort_if(in_array($room->status, ['closed', 'finished'], true), 410, 'الغرفة مغلقة.');
    }

    /** @return array<int,array<string,mixed>> */
    private function participants(Room $room, int $selfId): array
    {
        $staleBefore = now()->subSeconds(25);
        return $room->players()
            ->with('user.profile')
            ->where('is_bot', false)
            ->whereNotNull('user_id')
            ->get()
            ->map(function (RoomPlayer $player) use ($selfId, $staleBefore) {
                $online = $player->voice_joined_at !== null
                    && $player->voice_last_seen_at !== null
                    && $player->voice_last_seen_at->greaterThanOrEqualTo($staleBefore);
                return [
                    'user_id' => $player->user_id,
                    'name' => $player->user?->profile?->display_name ?: $player->user?->username ?: 'لاعب',
                    'avatar' => $player->user?->profile?->avatar,
                    'country_code' => safe_country_code($player->user?->profile?->country_code ?? 'PS'),
                    'flag' => (string)(config('countries.'.safe_country_code($player->user?->profile?->country_code ?? 'PS').'.flag') ?? '🇵🇸'),
                    'muted' => (bool) $player->voice_muted,
                    'deafened' => (bool) $player->voice_deafened,
                    'online' => $online,
                    'self' => $player->user_id === $selfId,
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    private function iceServers(): array
    {
        $stunUrls = (array) config('voice.stun_urls', ['stun:stun.l.google.com:19302']);
        $servers = [['urls' => array_values(array_filter($stunUrls))]];

        $turnUrls = (array) config('voice.turn_urls', []);
        if ($turnUrls !== []) {
            $servers[] = [
                'urls' => array_values(array_filter($turnUrls)),
                'username' => (string) config('voice.turn_username', ''),
                'credential' => (string) config('voice.turn_credential', ''),
            ];
        }

        return $servers;
    }
}
