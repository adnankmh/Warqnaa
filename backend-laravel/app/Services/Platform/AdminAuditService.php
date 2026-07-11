<?php
namespace App\Services\Platform;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminAuditService
{
    /** @param array<string,mixed>|null $before @param array<string,mixed>|null $after @param array<string,mixed> $meta */
    public function record(Request $request, string $action, Model|string|null $target = null, ?array $before = null, ?array $after = null, array $meta = []): void
    {
        if (!Schema::hasTable('admin_audit_logs')) return;
        $targetType = $target instanceof Model ? $target::class : (is_string($target) ? $target : null);
        $targetId = $target instanceof Model ? $target->getKey() : null;
        AdminAuditLog::create([
            'admin_id' => $request->user()?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before' => $before,
            'after' => $after,
            'meta' => $meta,
            'request_id' => $request->attributes->get('request_id'),
            'ip' => $request->ip(),
        ]);
    }
}
