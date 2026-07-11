<?php

namespace App\Http\Controllers;

use App\Models\PushDevice;
use Illuminate\Http\Request;

class MobilePushController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:16', 'max:4096'],
            'platform' => ['required', 'in:android,ios,web'],
            'app_version' => ['nullable', 'string', 'max:32'],
            'app_build' => ['nullable', 'integer', 'min:1'],
        ]);

        $hash = hash('sha256', $data['token']);
        $device = PushDevice::updateOrCreate(
            ['token_hash' => $hash],
            [
                'user_id' => $request->user()->id,
                'token' => $data['token'],
                'platform' => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'app_build' => $data['app_build'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['ok' => true, 'device' => ['id' => $device->id, 'platform' => $device->platform]]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        PushDevice::query()
            ->where('user_id', $request->user()->id)
            ->where('token_hash', hash('sha256', $data['token']))
            ->delete();

        return response()->json(['ok' => true]);
    }
}
