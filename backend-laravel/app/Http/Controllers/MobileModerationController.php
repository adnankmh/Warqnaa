<?php
namespace App\Http\Controllers;

use App\Models\{UserReport,User};
use App\Services\Platform\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileModerationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);
        $status = (string) $request->query('status', 'open');
        $rows = UserReport::with(['reporter.profile','reportedUser.profile','reviewer.profile'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()->paginate(50);
        return response()->json(['ok' => true, 'reports' => $rows]);
    }

    public function resolve(Request $request, UserReport $report, AdminAuditService $audit)
    {
        abort_unless($request->user()?->is_admin, 403);
        $data = $request->validate([
            'status' => 'required|in:reviewing,resolved,dismissed',
            'resolution' => 'nullable|string|max:2000',
            'user_action' => 'nullable|in:none,warn,ban,unban',
        ]);
        $before = $report->toArray();
        DB::transaction(function () use ($report, $request, $data) {
            $report->update([
                'status' => $data['status'],
                'resolution' => $data['resolution'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
            $target = $report->reportedUser;
            if ($target && ($data['user_action'] ?? 'none') === 'ban') $target->update(['is_banned' => true]);
            if ($target && ($data['user_action'] ?? 'none') === 'unban') $target->update(['is_banned' => false]);
        });
        $audit->record($request, 'moderation.report.resolve', $report, $before, $report->fresh()->toArray(), ['user_action' => $data['user_action'] ?? 'none']);
        return response()->json(['ok' => true, 'message' => 'تم تحديث البلاغ وإجراء المراجعة.']);
    }
}
