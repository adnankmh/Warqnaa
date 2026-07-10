<?php

namespace App\Http\Controllers;

use App\Models\{Game,Room,StoreItem,User};
use App\Services\GameEngine\EngineRegistry;
use Illuminate\Http\Request;

class MobileAdminController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 403, 'هذه الصفحة للإدارة فقط');
    }

    public function dashboard(Request $request)
    {
        $this->guard($request);
        return response()->json([
            'ok' => true,
            'stats' => [
                'users' => User::count(),
                'online' => User::where('last_seen_at', '>=', now()->subMinutes(3))->count(),
                'games' => Game::where('active', true)->count(),
                'rooms' => Room::whereIn('status', ['waiting', 'bidding', 'playing'])->count(),
                'store_items' => StoreItem::where('active', true)->count(),
            ],
            'users' => User::with('profile', 'wallet')->latest()->limit(100)->get()->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
                'is_banned' => (bool) $user->is_banned,
                'level' => (int) ($user->profile?->level ?? 1),
                'tokens' => (string) ($user->wallet?->tokens ?? 0),
            ]),
            'games' => Game::orderBy('id')->get(),
            'store' => StoreItem::orderBy('category')->orderBy('price')->get(),
            'rooms' => Room::with('game')->latest()->limit(100)->get(),
            'engine_registry' => EngineRegistry::all(),
        ]);
    }

    public function updateGame(Request $request, Game $game)
    {
        $this->guard($request);
        $data = $request->validate([
            'active' => 'nullable|boolean',
            'min_players' => 'nullable|integer|min:1|max:8',
            'max_players' => 'nullable|integer|min:1|max:8',
            'partnership' => 'nullable|boolean',
            'name' => 'nullable|array',
            'rules' => 'nullable|array',
        ]);
        $game->update($data);
        return response()->json(['ok' => true, 'message' => 'تم تحديث اللعبة', 'game' => $game->fresh()]);
    }

    public function updateStore(Request $request, StoreItem $item)
    {
        $this->guard($request);
        $data = $request->validate([
            'price' => 'nullable|integer|min:0|max:9000000000000000000',
            'active' => 'nullable|boolean',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'name' => 'nullable|array',
            'payload' => 'nullable|array',
        ]);
        $item->update($data);
        return response()->json(['ok' => true, 'message' => 'تم تحديث عنصر المتجر', 'item' => $item->fresh()]);
    }

    public function userAction(Request $request, User $user)
    {
        $this->guard($request);
        $data = $request->validate([
            'action' => 'required|in:ban,unban,grant_tokens,set_level',
            'amount' => 'nullable|integer|min:0|max:1000000000000',
            'level' => 'nullable|integer|min:1|max:999',
        ]);
        match ($data['action']) {
            'ban' => $user->update(['is_banned' => true]),
            'unban' => $user->update(['is_banned' => false]),
            'grant_tokens' => $user->wallet()->firstOrCreate(['user_id' => $user->id], ['tokens' => 50])->increment('tokens', (int) ($data['amount'] ?? 0)),
            'set_level' => $user->profile?->update(['level' => (int) ($data['level'] ?? 1)]),
        };
        return response()->json(['ok' => true, 'message' => 'تم تنفيذ الإجراء']);
    }
}
