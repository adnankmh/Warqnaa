<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $indexes = [
            ['messages','idx_messages_room_created','room_id, created_at'],
            ['messages','idx_messages_private_thread','sender_id, receiver_id, created_at'],
            ['notifications','idx_notifications_user_read_created','user_id, read, created_at'],
            ['friendships','idx_friendships_status_users','status, requester_id, addressee_id'],
            ['rooms','idx_rooms_game_status_created','game_id, status, created_at'],
            ['room_players','idx_room_players_room_user','room_id, user_id'],
            ['store_items','idx_store_items_category_active','category, active'],
            ['inventory_items','idx_inventory_user_active','user_id, active'],
        ];
        foreach ($indexes as [$table,$name,$cols]) {
            if (!Schema::hasTable($table)) continue;
            try { DB::statement("CREATE INDEX {$name} ON {$table} ({$cols})"); } catch (Throwable $e) {}
        }
    }
    public function down(): void
    {
        foreach (['idx_messages_room_created','idx_messages_private_thread','idx_notifications_user_read_created','idx_friendships_status_users','idx_rooms_game_status_created','idx_room_players_room_user','idx_store_items_category_active','idx_inventory_user_active'] as $name) {
            foreach (['messages','notifications','friendships','rooms','room_players','store_items','inventory_items'] as $table) {
                try { DB::statement("DROP INDEX {$name} ON {$table}"); } catch (Throwable $e) {}
            }
        }
    }
};
