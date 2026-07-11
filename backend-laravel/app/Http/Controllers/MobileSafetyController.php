<?php
namespace App\Http\Controllers;

use App\Models\{Message,Room,User,UserReport};
use Illuminate\Http\Request;

class MobileSafetyController extends Controller
{
    public function report(Request $request)
    {
        $data = $request->validate([
            'reported_user_id' => 'nullable|integer|exists:users,id',
            'room_code' => 'nullable|string|max:30',
            'message_id' => 'nullable|integer|exists:messages,id',
            'category' => 'required|in:harassment,abuse,cheating,spam,impersonation,inappropriate_content,other',
            'details' => 'nullable|string|max:2000',
            'evidence' => 'nullable|array|max:10',
        ]);
        abort_if((int) ($data['reported_user_id'] ?? 0) === (int) $request->user()->id, 422, 'لا يمكنك الإبلاغ عن حسابك.');
        $room = !empty($data['room_code']) ? Room::where('code', $data['room_code'])->first() : null;
        $message = !empty($data['message_id']) ? Message::find($data['message_id']) : null;
        $reported = !empty($data['reported_user_id']) ? User::find($data['reported_user_id']) : $message?->sender;
        abort_unless($reported || $room || $message, 422, 'اختر لاعبًا أو رسالة أو غرفة للإبلاغ.');

        $recentDuplicate = UserReport::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reported_user_id', $reported?->id)
            ->where('category', $data['category'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
        abort_if($recentDuplicate, 429, 'تم إرسال بلاغ مماثل مؤخرًا.');

        $report = UserReport::create([
            'reporter_id' => $request->user()->id,
            'reported_user_id' => $reported?->id,
            'room_id' => $room?->id,
            'message_id' => $message?->id,
            'category' => $data['category'],
            'details' => $data['details'] ?? null,
            'evidence' => $data['evidence'] ?? [],
            'status' => 'open',
        ]);
        return response()->json(['ok' => true, 'message' => 'تم إرسال البلاغ إلى فريق المراجعة.', 'report_id' => $report->id], 201);
    }

    public function mine(Request $request)
    {
        $rows = UserReport::query()->where('reporter_id', $request->user()->id)->latest()->limit(100)->get();
        return response()->json(['ok' => true, 'reports' => $rows->map(fn ($row) => [
            'id' => $row->id,
            'category' => $row->category,
            'status' => $row->status,
            'details' => $row->details,
            'created_at' => $row->created_at?->toIso8601String(),
            'reviewed_at' => $row->reviewed_at?->toIso8601String(),
            'resolution' => $row->resolution,
        ])]);
    }
}
