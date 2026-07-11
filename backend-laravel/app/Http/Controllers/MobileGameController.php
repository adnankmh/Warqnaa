<?php

namespace App\Http\Controllers;

use App\Models\{Game,Message,Room,RoomPlayer};
use App\Services\GameEngine\{EngineRegistry,GameFactory,GameRuleContract};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Hash};
use Illuminate\Support\Str;
use App\Services\Platform\ProductionConfigService;

class MobileGameController extends Controller
{
    public function catalog(Request $request)
    {
        $dbGames = Game::query()->where('active', true)->get()->keyBy('key');
        $catalog = collect(EngineRegistry::all())->map(function (array $meta, string $key) use ($dbGames) {
            $game = $dbGames->get($key);
            return [
                'key' => $key,
                'name' => $game?->name ?: $meta['name'],
                'min_players' => (int) ($game?->min_players ?: $meta['min']),
                'max_players' => (int) ($game?->max_players ?: $meta['max']),
                'partnership' => (bool) ($game?->partnership ?? $meta['partnership']),
                'engine' => $meta['engine'],
                'hand_size' => $meta['hand'],
                'deck_size' => $meta['deck'],
                'actions' => $meta['actions'],
                'rules' => $meta['rules'],
                'free_play' => true,
                'server_authoritative' => true,
            ];
        })->values();

        return response()->json(['ok' => true, 'games' => $catalog]);
    }

    public function rules(string $gameKey)
    {
        $meta = EngineRegistry::get($gameKey);
        abort_unless($meta, 404, 'اللعبة غير موجودة');
        return response()->json(['ok' => true, 'key' => $gameKey, 'game' => $meta]);
    }

    public function rooms(Request $request, string $gameKey)
    {
        $game = Game::where('key', $gameKey)->first();
        if (!$game) return response()->json(['ok' => true, 'rooms' => []]);

        $rooms = Room::query()
            ->with(['players.user.profile', 'game'])
            ->where('game_id', $game->id)
            ->whereIn('status', ['waiting', 'bidding', 'playing'])
            ->where('visibility', '!=', 'private')
            ->latest('updated_at')
            ->limit(30)
            ->get()
            ->filter(fn (Room $room) => $room->players->where('is_bot', false)->where('connected', true)->count() > 0)
            ->map(function (Room $room) {
                $state = $room->state ?: [];
                $realPlayers = $room->players->where('is_bot', false)->count();
                return [
                    'code' => $room->code,
                    'name' => $state['room_name'] ?? ($room->game?->name . ' Room'),
                    'voice_enabled' => (bool) ($state['voice_enabled'] ?? $state['voice_room'] ?? false),
                    'visibility' => $room->visibility,
                    'players' => $realPlayers,
                    'max_players' => $room->max_players,
                    'turn_seconds' => (int) ($state['turn_seconds'] ?? 10),
                    'owner' => $room->owner?->username,
                ];
            })->values();

        return response()->json(['ok' => true, 'rooms' => $rooms]);
    }

    public function join(Request $request, Room $room)
    {
        $data = $request->validate(['password' => 'nullable|string|max:40']);
        $room->loadMissing(['game', 'players.user.profile']);
        abort_if(in_array($room->status, ['closed', 'finished'], true), 410, 'الغرفة مغلقة.');

        if ($room->visibility === 'private') {
            abort_unless($room->password && Hash::check((string) ($data['password'] ?? ''), $room->password), 403, 'كلمة سر الغرفة غير صحيحة.');
        }

        $user = $request->user();
        $otherRoom = RoomPlayer::query()
            ->where('user_id', $user->id)
            ->where('connected', true)
            ->where('room_id', '!=', $room->id)
            ->whereHas('room', fn ($query) => $query->whereNotIn('status', ['closed', 'finished']))
            ->exists();
        abort_if($otherRoom, 409, 'أنت داخل لعبة أخرى. غادرها أولاً.');

        $existing = $room->players()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->update(['connected' => true, 'missed_turns' => 0]);
            return response()->json(['ok' => true, 'message' => 'عدت إلى مقعدك.', 'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $user->id)]);
        }

        $botSeat = $room->players()->where('is_bot', true)->orderBy('seat')->first();
        abort_unless($botSeat, 422, 'الغرفة ممتلئة ولا يوجد مقعد متاح.');

        DB::transaction(function () use ($room, $botSeat, $user) {
            $oldKey = 'bot:' . ($botSeat->bot_key ?: $botSeat->id);
            $newKey = 'user:' . $user->id;
            $state = $this->replacePlayerKey($room->state ?: [], $oldKey, $newKey);
            $state['messages'][] = '👤 انضم ' . $user->username . ' إلى المقعد ' . ((int) $botSeat->seat + 1) . '.';
            $room->update(['state' => $state]);
            $botSeat->update([
                'user_id' => $user->id,
                'bot_key' => null,
                'is_bot' => false,
                'connected' => true,
                'missed_turns' => 0,
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'تم الانضمام إلى الغرفة.',
            'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $user->id),
        ], 201);
    }

    public function create(Request $request, ProductionConfigService $productionConfig)
    {
        $data = $request->validate([
            'game' => 'required|string|max:80',
            'target' => 'nullable|integer|min:1|max:10000',
            'turn_seconds' => 'nullable|integer|in:5,7,10',
            'visibility' => 'nullable|in:public,friends,private',
            'password' => 'nullable|string|min:3|max:40',
            'bots' => 'nullable|integer|min:0|max:7',
            'voice_enabled' => 'nullable|boolean',
            'room_name' => 'nullable|string|max:60',
        ]);
        if (!empty($data['voice_enabled']) && !$productionConfig->enabled('voice_rooms', true)) {
            return response()->json(['ok'=>false,'message'=>'الغرف الصوتية متوقفة مؤقتًا.'], 503);
        }
        $visibility = $data['visibility'] ?? 'private';
        if ($visibility === 'private' && empty($data['password'])) {
            return response()->json([
                'ok' => false,
                'message' => 'كلمة السر مطلوبة عند إنشاء غرفة خاصة.',
                'errors' => ['password' => ['كلمة السر مطلوبة عند إنشاء غرفة خاصة.']],
            ], 422);
        }

        $meta = EngineRegistry::get($data['game']);
        abort_unless($meta, 422, 'محرك اللعبة غير مدعوم');

        $user = $request->user();
        $game = Game::firstOrCreate(
            ['key' => $data['game']],
            [
                'name' => $meta['name'],
                'min_players' => $meta['min'],
                'max_players' => $meta['max'],
                'partnership' => $meta['partnership'],
                'rules' => ['engine' => $meta['engine'], 'summary' => $meta['rules']],
                'active' => true,
            ]
        );

        $maxPlayers = (int) $game->max_players;
        $botCount = min($maxPlayers - 1, max((int) ($data['bots'] ?? ($maxPlayers - 1)), 0));
        $playerKeys = ['user:' . $user->id];
        for ($i = 1; $i <= $botCount; $i++) {
            $playerKeys[] = 'bot:' . $this->botName($i - 1);
        }
        while (count($playerKeys) < $maxPlayers) {
            $playerKeys[] = 'bot:' . $this->botName(count($playerKeys) - 1);
        }

        $target = (int) ($data['target'] ?? $this->defaultTarget($data['game']));
        $engine = GameFactory::make($data['game']);
        $state = $engine->initialState($playerKeys, [
            'target' => $target,
            'turn_seconds' => (int) ($data['turn_seconds'] ?? 10),
            'partners' => (bool) $game->partnership,
        ]);
        $state['game'] = $data['game'];
        $state['mobile_api'] = true;
        $state['free_play'] = true;
        $state['entry_fee'] = 0;
        $state['room_name'] = trim((string) ($data['room_name'] ?? ($meta['name'] . ' • ' . $user->username)));
        $state['voice_enabled'] = (bool) ($data['voice_enabled'] ?? false);
        $state['voice_room'] = $state['voice_enabled'];
        $state['voice_fee'] = 0;
        $state['turn_seconds'] = (int) ($data['turn_seconds'] ?? 10);
        $state['messages'] = array_values(array_merge($state['messages'] ?? [], [
            '🎮 تم إنشاء غرفة مجانية. لا يتم خصم أي توكنز أثناء اللعب.',
            '🛡️ جميع الحركات تُراجع من المحرك على الخادم قبل اعتمادها.',
            !empty($state['voice_enabled'])
                ? '🎙️ هذه غرفة صوتية. يتم طلب إذن الميكروفون فقط بعد دخولها ويمكن لكل لاعب الكتم محليًا.'
                : '🃏 هذه غرفة عادية بدون محادثة صوتية.',
        ]));

        $room = DB::transaction(function () use ($game, $user, $visibility, $data, $maxPlayers, $target, $state, $playerKeys) {
            $room = Room::create([
                'code' => $this->uniqueCode(),
                'game_id' => $game->id,
                'owner_id' => $user->id,
                'visibility' => $visibility,
                'password' => $visibility === 'private' && !empty($data['password']) ? Hash::make($data['password']) : null,
                'entry_fee' => 0,
                'min_level' => 1,
                'status' => $this->roomStatus((string) ($state['phase'] ?? 'playing')),
                'max_players' => $maxPlayers,
                'target_score' => (string) $target,
                'state' => $state,
                'started_at' => now(),
            ]);

            foreach ($playerKeys as $index => $key) {
                $isBot = str_starts_with($key, 'bot:');
                RoomPlayer::create([
                    'room_id' => $room->id,
                    'user_id' => $isBot ? null : $user->id,
                    'bot_key' => $isBot ? mb_substr($key, 4) : null,
                    'seat' => (string) $index,
                    'is_bot' => $isBot,
                    'connected' => true,
                ]);
            }
            return $room;
        });

        return response()->json([
            'ok' => true,
            'message' => 'تم إنشاء اللعبة بنجاح',
            'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $user->id),
        ], 201);
    }

    public function show(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room);
        return response()->json(['ok' => true, 'room' => $this->roomPayload($room->load(['game', 'players.user.profile']), $request->user()->id)]);
    }

    public function action(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room);
        $data = $request->validate([
            'action' => 'required|string|max:80',
            'payload' => 'nullable|array',
        ]);
        $user = $request->user();
        $state = $room->state ?: [];
        $playerKey = 'user:' . $user->id;
        $engine = GameFactory::make($room->game->key);
        $payload = $data['payload'] ?? [];
        $valid = $engine->validate($state, $playerKey, $data['action'], $payload);

        DB::table('game_actions')->insert([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'action' => $data['action'],
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'valid' => $valid,
            'ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$valid) {
            return response()->json([
                'ok' => false,
                'message' => $state['last_error_message'] ?? 'الحركة غير قانونية في الحالة الحالية.',
                'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $user->id),
            ], 422);
        }

        $next = $engine->apply($state, $playerKey, $data['action'], $payload);
        $next = $this->advanceAutomatedTurns($engine, $next, (string) $room->game->key);
        $room->update([
            'state' => $next,
            'status' => $this->roomStatus((string) ($next['phase'] ?? 'playing')),
            'finished_at' => ($next['phase'] ?? null) === 'finished' ? now() : null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'تم اعتماد الحركة',
            'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $user->id),
        ]);
    }

    public function timeout(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room);
        $state = $room->state ?: [];
        $engine = GameFactory::make($room->game->key);
        if (method_exists($engine, 'onTurnTimeout')) {
            $state = $engine->onTurnTimeout($state);
        } else {
            $state = $this->automaticMove($engine, $state, (string) $room->game->key);
        }
        $state = $this->advanceAutomatedTurns($engine, $state, (string) $room->game->key);
        $room->update(['state' => $state, 'status' => $this->roomStatus((string) ($state['phase'] ?? 'playing'))]);
        return response()->json(['ok' => true, 'room' => $this->roomPayload($room->fresh(['game', 'players.user.profile']), $request->user()->id)]);
    }

    public function leave(Request $request, Room $room)
    {
        $player = $room->players()->where('user_id', $request->user()->id)->first();
        abort_unless($player, 403, 'أنت لست داخل هذه الغرفة');
        $player->update(['connected' => false]);
        return response()->json(['ok' => true, 'message' => 'تمت مغادرة الغرفة دون خصم توكنز.']);
    }

    public function chat(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room);
        $messages = Message::with('sender.profile')
            ->where('room_id', $room->id)
            ->latest()->limit(100)->get()->reverse()->values();
        return response()->json([
            'ok' => true,
            'messages' => $messages->map(fn (Message $message) => [
                'id' => $message->id,
                'mine' => $message->sender_id === $request->user()->id,
                'name' => $message->sender?->profile?->display_name ?: $message->sender?->username ?: 'لاعب',
                'body' => $message->body,
                'color' => $message->sender?->profile?->chat_color ?: '#ffffff',
                'time' => $message->created_at?->format('H:i'),
            ]),
        ]);
    }

    public function sendChat(Request $request, Room $room)
    {
        $this->authorizeRoom($request, $room);
        $data = $request->validate(['body' => 'required|string|max:500']);
        $body = $this->cleanChat((string) $data['body']);
        abort_if($body === '', 422, 'لا يمكن إرسال رسالة فارغة');
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'room_id' => $room->id,
            'body' => $body,
        ]);
        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $message->id,
                'mine' => true,
                'name' => $request->user()->profile?->display_name ?: $request->user()->username,
                'body' => $message->body,
                'color' => $request->user()->profile?->chat_color ?: '#ffffff',
                'time' => $message->created_at?->format('H:i'),
            ],
        ], 201);
    }

    private function cleanChat(string $body): string
    {
        $body = trim(strip_tags($body));
        $blocked = ['fuck','shit','bitch','asshole','خرا','شرموط','قحبة','نيك'];
        foreach ($blocked as $word) {
            $body = preg_replace('/' . preg_quote($word, '/') . '/iu', '***', $body);
        }
        return mb_substr($body, 0, 500);
    }

    /** @return array<string,mixed> */
    private function roomPayload(Room $room, int $userId): array
    {
        $state = $this->publicState($room->state ?: [], 'user:' . $userId);
        return [
            'id' => $room->id,
            'code' => $room->code,
            'game' => $room->game?->key,
            'status' => $room->status,
            'visibility' => $room->visibility,
            'entry_fee' => 0,
            'room_name' => $state['room_name'] ?? ($room->game?->name ?? 'غرفة ورقنا'),
            'voice_enabled' => (bool) ($state['voice_enabled'] ?? $state['voice_room'] ?? false),
            'turn_seconds' => (int) ($state['turn_seconds'] ?? 10),
            'players' => $room->players->map(function (RoomPlayer $player) {
                return [
                    'key' => $player->is_bot ? 'bot:' . ($player->bot_key ?: $player->id) : 'user:' . $player->user_id,
                    'name' => $player->is_bot ? ($player->bot_key ?: 'بوت') : ($player->user?->profile?->display_name ?: $player->user?->username),
                    'seat' => (int) $player->seat,
                    'bot' => (bool) $player->is_bot,
                    'connected' => (bool) $player->connected,
                    'voice_muted' => (bool) ($player->voice_muted ?? false),
                    'voice_deafened' => (bool) ($player->voice_deafened ?? false),
                    'avatar' => $player->user?->profile?->avatar,
                    'badge' => $player->user?->profile?->badge,
                ];
            })->values(),
            'state' => $state,
            'updated_at' => $room->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string,mixed> */
    private function publicState(array $state, string $myKey): array
    {
        $copy = $state;
        $hands = $state['hands'] ?? [];
        $copy['hand'] = array_values($hands[$myKey] ?? []);
        $copy['hand_counts'] = [];
        foreach ($hands as $key => $cards) {
            $copy['hand_counts'][$key] = is_array($cards) ? count($cards) : 0;
        }
        unset($copy['hands'], $copy['_tarneeb_v2'], $copy['_global_engine']);
        if (isset($copy['deck']) && is_array($copy['deck'])) {
            $copy['deck_count'] = count($copy['deck']);
            unset($copy['deck']);
        }
        if (isset($copy['boneyard']) && is_array($copy['boneyard'])) {
            $copy['boneyard_count'] = count($copy['boneyard']);
            unset($copy['boneyard']);
        }
        $copy['legal_cards'] = [];
        $copy['available_actions'] = [];
        if (($copy['turn'] ?? null) === $myKey) {
            try {
                $engine = GameFactory::make((string) ($state['game'] ?? $state['game_type'] ?? 'tarneeb'));
                if (method_exists($engine, 'availableActions')) {
                    $copy['available_actions'] = $engine->availableActions($state, $myKey);
                }
                foreach ($copy['hand'] as $card) {
                    foreach (['play_card', 'discard', 'play_tile', 'move_to_foundation'] as $candidate) {
                        if ($engine->validate($state, $myKey, $candidate, ['card' => $card, 'tile' => $card])) {
                            $copy['legal_cards'][] = $card;
                            break;
                        }
                    }
                }
            } catch (\Throwable) {
                $copy['legal_cards'] = $copy['hand'];
            }
        }
        $copy['you'] = $myKey;
        $copy['free_play'] = true;
        return $copy;
    }

    /**
     * Advances all bot/away seats on the server. This keeps the game moving in
     * portrait, landscape and background/resume scenarios without trusting the client.
     *
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    private function advanceAutomatedTurns(GameRuleContract $engine, array $state, string $gameKey, int $guard = 80): array
    {
        for ($step = 0; $step < $guard; $step++) {
            $phase = (string) ($state['phase'] ?? '');
            if (in_array($phase, ['finished', 'game_over', 'round_end'], true)) break;
            $turn = (string) ($state['turn'] ?? '');
            if ($turn === '' || !str_starts_with($turn, 'bot:')) break;
            $before = hash('sha256', json_encode([$turn, $phase, $state['hands'][$turn] ?? null, $state['board'] ?? null, $state['moves_left'] ?? null]));
            $state = $this->automaticMove($engine, $state, $gameKey);
            $after = hash('sha256', json_encode([$state['turn'] ?? null, $state['phase'] ?? null, $state['hands'][$turn] ?? null, $state['board'] ?? null, $state['moves_left'] ?? null]));
            if ($before === $after) {
                $state['messages'][] = 'تعذر إيجاد حركة آلية قانونية؛ تم إيقاف الدور الآلي للحماية.';
                break;
            }
        }
        return $state;
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function automaticMove(GameRuleContract $engine, array $state, string $gameKey): array
    {
        $player = (string) ($state['turn'] ?? '');
        if ($player === '') return $state;

        // Uploaded final engines include their own rule-aware bot policy.
        if (method_exists($engine, 'onTurnTimeout')) {
            return $engine->onTurnTimeout($state);
        }

        if ($gameKey === 'domino') {
            foreach (($state['hands'][$player] ?? []) as $tile) {
                foreach (['right', 'left'] as $side) {
                    $payload = ['tile' => $tile, 'side' => $side];
                    if ($engine->validate($state, $player, 'play_tile', $payload)) {
                        return $engine->apply($state, $player, 'play_tile', $payload);
                    }
                }
            }
            if ($engine->validate($state, $player, 'draw', [])) return $engine->apply($state, $player, 'draw', []);
            if ($engine->validate($state, $player, 'pass', [])) return $engine->apply($state, $player, 'pass', []);
        }

        if ($gameKey === 'basra') {
            foreach (($state['hands'][$player] ?? []) as $card) {
                $payload = ['card' => $card];
                if ($engine->validate($state, $player, 'play_card', $payload)) {
                    return $engine->apply($state, $player, 'play_card', $payload);
                }
            }
        }

        if ($gameKey === 'backgammon') {
            if (empty($state['moves_left']) && $engine->validate($state, $player, 'roll', [])) {
                return $engine->apply($state, $player, 'roll', []);
            }
            foreach (($state['moves_left'] ?? []) as $distance) {
                foreach (($state['points'] ?? []) as $from => $point) {
                    if (($point['owner'] ?? null) !== $player || (int) ($point['count'] ?? 0) < 1) continue;
                    foreach ([(int) $from + (int) $distance, (int) $from - (int) $distance] as $to) {
                        $candidate = $engine->apply($state, $player, 'move', ['from' => (int) $from, 'to' => $to]);
                        if (($candidate['last_error'] ?? null) === null && $candidate !== $state) return $candidate;
                    }
                }
            }
            if ($engine->validate($state, $player, 'pass', [])) return $engine->apply($state, $player, 'pass', []);
        }

        // Safe fallback for universal/board engines.
        foreach ([
            ['play_card', ['card' => $state['hands'][$player][0] ?? null]],
            ['play_tile', ['tile' => $state['hands'][$player][0] ?? null, 'side' => 'right']],
            ['roll_dice', []],
            ['roll', []],
            ['pass', []],
            ['move', ['from' => 1, 'to' => 2]],
        ] as [$action, $payload]) {
            $payload = array_filter($payload, fn ($value) => $value !== null);
            if ($engine->validate($state, $player, $action, $payload)) {
                return $engine->apply($state, $player, $action, $payload);
            }
        }

        return $state;
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function replacePlayerKey(array $state, string $oldKey, string $newKey): array
    {
        $replace = function ($value) use (&$replace, $oldKey, $newKey) {
            if (is_array($value)) {
                $next = [];
                foreach ($value as $key => $item) {
                    $mappedKey = is_string($key) && $key === $oldKey ? $newKey : $key;
                    $next[$mappedKey] = $replace($item);
                }
                return $next;
            }
            return is_string($value) && $value === $oldKey ? $newKey : $value;
        };
        return $replace($state);
    }

    private function authorizeRoom(Request $request, Room $room): void
    {
        abort_unless($room->players()->where('user_id', $request->user()->id)->exists(), 403, 'لا تملك صلاحية هذه الغرفة');
        $room->loadMissing('game');
    }

    private function uniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(7));
        } while (Room::where('code', $code)->exists());
        return $code;
    }

    private function defaultTarget(string $key): int
    {
        return match ($key) {
            'tarneeb', 'tarneeb_41' => 41,
            'tarneeb_61', 'syrian_tarneeb' => 61,
            'tarneeb_400' => 400,
            'baloot' => 152,
            default => 101,
        };
    }

    private function roomStatus(string $phase): string
    {
        return match ($phase) {
            'bidding' => 'bidding',
            'finished', 'game_over', 'round_end' => 'finished',
            'waiting', 'new' => 'waiting',
            default => 'playing',
        };
    }

    private function botName(int $seat): string
    {
        return ['عاصم', 'جميل', 'ليلى', 'سامر', 'نور', 'كريم', 'رنا', 'يزن'][$seat % 8];
    }
}
