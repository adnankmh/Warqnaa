<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_verified_at')) $table->timestamp('email_verified_at')->nullable()->after('email');
            if (!Schema::hasColumn('users', 'deletion_requested_at')) $table->timestamp('deletion_requested_at')->nullable()->after('last_seen_at');
            if (!Schema::hasColumn('users', 'last_login_ip')) $table->string('last_login_ip', 64)->nullable()->after('last_seen_at');
            if (!Schema::hasColumn('users', 'last_login_user_agent')) $table->string('last_login_user_agent', 500)->nullable()->after('last_login_ip');
        });

        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_reports')) {
            Schema::create('user_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->foreignId('message_id')->nullable()->constrained('messages')->nullOnDelete();
                $table->string('category', 40);
                $table->text('details')->nullable();
                $table->json('evidence')->nullable();
                $table->string('status', 24)->default('open');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('resolution')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
                $table->index(['reported_user_id', 'status']);
            });
        }

        if (!Schema::hasTable('admin_audit_logs')) {
            Schema::create('admin_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 100);
                $table->string('target_type', 120)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->json('meta')->nullable();
                $table->string('request_id', 64)->nullable();
                $table->string('ip', 64)->nullable();
                $table->timestamps();
                $table->index(['action', 'created_at']);
                $table->index(['target_type', 'target_id']);
            });
        }

        if (!Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->boolean('enabled')->default(false);
                $table->json('payload')->nullable();
                $table->string('environment', 32)->default('all');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('app_releases')) {
            Schema::create('app_releases', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 20);
                $table->string('version', 40);
                $table->unsignedInteger('build_number')->default(1);
                $table->boolean('required')->default(false);
                $table->boolean('active')->default(true);
                $table->text('notes')->nullable();
                $table->string('download_url', 500)->nullable();
                $table->timestamps();
                $table->unique(['platform', 'version', 'build_number'], 'app_release_unique');
                $table->index(['platform', 'active']);
            });
        }

        if (!Schema::hasTable('account_deletion_requests')) {
            Schema::create('account_deletion_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('status', 24)->default('pending');
                $table->timestamp('requested_at');
                $table->timestamp('scheduled_for')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('reason', 500)->nullable();
                $table->timestamps();
                $table->index(['status', 'scheduled_for']);
            });
        }

        $flags = [
            ['key' => 'voice_rooms', 'enabled' => true, 'payload' => ['mode' => 'webrtc']],
            ['key' => 'rewarded_ads', 'enabled' => true, 'payload' => ['daily_limit' => 5]],
            ['key' => 'groups', 'enabled' => true, 'payload' => ['weekly_leagues' => true]],
            ['key' => 'competitions', 'enabled' => true, 'payload' => ['max_stages' => 4]],
            ['key' => 'token_transfers', 'enabled' => true, 'payload' => ['fee_percent' => 10]],
            ['key' => 'maintenance_mode', 'enabled' => false, 'payload' => ['message' => '']],
            ['key' => 'local_demo_mode', 'enabled' => false, 'payload' => ['production_allowed' => false]],
        ];
        foreach ($flags as $flag) {
            DB::table('feature_flags')->updateOrInsert(
                ['key' => $flag['key']],
                [
                    'enabled' => $flag['enabled'],
                    'payload' => json_encode($flag['payload'], JSON_UNESCAPED_UNICODE),
                    'environment' => 'all',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach (['web', 'android', 'ios'] as $platform) {
            DB::table('app_releases')->updateOrInsert(
                ['platform' => $platform, 'version' => '1.53.0', 'build_number' => 153],
                [
                    'required' => false,
                    'active' => true,
                    'notes' => 'Warqna v153 production launch foundation',
                    'download_url' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');
        Schema::dropIfExists('app_releases');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('user_reports');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personal_access_tokens');

        Schema::table('users', function (Blueprint $table) {
            foreach (['email_verified_at', 'deletion_requested_at', 'last_login_ip', 'last_login_user_agent'] as $column) {
                if (Schema::hasColumn('users', $column)) $table->dropColumn($column);
            }
        });
    }
};
