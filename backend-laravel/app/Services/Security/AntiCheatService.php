<?php
namespace App\Services\Security;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

class AntiCheatService
{
    public function log(Room $room, ?int $userId, string $event, int $severity = 1, array $meta = []): void
    {
        DB::table('anti_cheat_events')->insert([
            'room_id'=>$room->id,
            'user_id'=>$userId,
            'event'=>$event,
            'severity'=>$severity,
            'meta'=>json_encode($meta, JSON_UNESCAPED_UNICODE),
            'ip'=>request()?->ip(),
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
    }

    public function tooFast(Room $room, ?int $userId, int $minSeconds = 1): bool
    {
        if (!$userId) return false;
        $last = DB::table('game_actions')->where('room_id',$room->id)->where('user_id',$userId)->latest('id')->first();
        if ($last && now()->diffInSeconds($last->created_at) < $minSeconds) {
            $this->log($room, $userId, 'too_fast_action', 2, ['min_seconds'=>$minSeconds]);
            return true;
        }
        return false;
    }

    public function validateSeatTurn(Room $room, int $userId): bool
    {
        $state = $room->state ?: [];
        if (isset($state['turn']) && str_starts_with((string)$state['turn'], 'user:')) {
            $ok = (string)$state['turn'] === 'user:'.$userId;
            if (!$ok) $this->log($room, $userId, 'wrong_turn_action', 3, ['turn'=>$state['turn']]);
            return $ok;
        }
        if (!isset($state['turn_user_id'])) return true;
        $ok = (int) $state['turn_user_id'] === $userId;
        if (!$ok) $this->log($room, $userId, 'wrong_turn_action', 3, ['turn_user_id'=>$state['turn_user_id']]);
        return $ok;
    }
}
