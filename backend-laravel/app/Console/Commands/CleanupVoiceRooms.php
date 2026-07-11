<?php

namespace App\Console\Commands;

use App\Models\RoomPlayer;
use App\Models\VoiceSignal;
use Illuminate\Console\Command;

class CleanupVoiceRooms extends Command
{
    protected $signature = 'warqna:cleanup-voice {--presence-seconds=45 : Seconds before a voice participant is marked offline}';

    protected $description = 'Remove expired WebRTC signaling records and clear stale voice presence.';

    public function handle(): int
    {
        $seconds = max(30, min(300, (int) $this->option('presence-seconds')));
        $staleBefore = now()->subSeconds($seconds);

        $presence = RoomPlayer::query()
            ->whereNotNull('voice_last_seen_at')
            ->where('voice_last_seen_at', '<', $staleBefore)
            ->update([
                'voice_joined_at' => null,
                'voice_last_seen_at' => null,
                'voice_muted' => true,
                'voice_deafened' => true,
            ]);

        $signals = VoiceSignal::query()
            ->where(function ($query) {
                $query->where('created_at', '<', now()->subMinutes(10))
                    ->orWhere(function ($delivered) {
                        $delivered->whereNotNull('delivered_at')
                            ->where('delivered_at', '<', now()->subMinutes(2));
                    });
            })
            ->delete();

        $this->info("Voice cleanup complete: {$presence} stale presences, {$signals} signals removed.");

        return self::SUCCESS;
    }
}
