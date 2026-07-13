<?php

use App\Models\AdminDesignerEntity;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (class_exists(AdminDesignerEntity::class)) {
            AdminDesignerEntity::query()->updateOrCreate(
                ['entity_type'=>'system','key'=>'online_only','locale'=>'all'],
                [
                    'payload'=>['enabled'=>false,'offline_login'=>true,'offline_gameplay'=>true,'server_sync_when_online'=>true],
                    'sort_order'=>1,'active'=>true,'revision'=>174,
                ]
            );
        }
    }

    public function down(): void
    {
        if (class_exists(AdminDesignerEntity::class)) {
            AdminDesignerEntity::query()->where('entity_type','system')->where('key','online_only')->update([
                'payload'=>['enabled'=>true,'offline_login'=>false,'offline_gameplay'=>false],
            ]);
        }
    }
};
