<?php

use App\Services\WarqnaPro\StoreCatalogService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_items')) return;

        // Re-run the canonical server catalog so mobile IDs, prices and active
        // flags are authoritative before the user attempts a purchase.
        app(StoreCatalogService::class)->sync();

        $items = [
            ['badge_fairplay','شارة اللعب النظيف','Fair Play Badge','badge',30000,null,['badge'=>'badge_fairplay','preview_icon'=>'🛡️']],
            ['cover_phoenix','غلاف العنقاء الذهبية','Golden Phoenix Cover','profile_cover',44000,null,['cover'=>'cover_phoenix','preview_icon'=>'🔥']],
            ['cover_ocean','غلاف موج المحيط','Ocean Wave Cover','profile_cover',18000,null,['cover'=>'cover_ocean','preview_icon'=>'🌊']],
            ['cover_neon','غلاف مدينة النيون','Neon City Cover','profile_cover',29000,null,['cover'=>'cover_neon','preview_icon'=>'🌃']],
            ['cover_forest','غلاف الغابة الملكية','Royal Forest Cover','profile_cover',21000,null,['cover'=>'cover_forest','preview_icon'=>'🌲']],
            ['cover_sunset','غلاف غروب فاخر','Luxury Sunset Cover','profile_cover',23000,null,['cover'=>'cover_sunset','preview_icon'=>'🌅']],
            ['cover_ice','غلاف الكريستال الجليدي','Ice Crystal Cover','profile_cover',27000,null,['cover'=>'cover_ice','preview_icon'=>'❄️']],
            ['cover_tiger','غلاف هيبة النمر','Tiger Prestige Cover','profile_cover',36000,null,['cover'=>'cover_tiger','preview_icon'=>'🐯']],
            ['cover_eagle','غلاف جناح النسر','Eagle Wing Cover','profile_cover',38000,null,['cover'=>'cover_eagle','preview_icon'=>'🦅']],
            ['cover_lava','غلاف الحمم الأسطورية','Legendary Lava Cover','profile_cover',47000,null,['cover'=>'cover_lava','preview_icon'=>'🌋']],
            ['cover_pearl','غلاف لؤلؤة القصر','Palace Pearl Cover','profile_cover',52000,null,['cover'=>'cover_pearl','preview_icon'=>'🦪']],
        ];
        foreach ($items as [$key,$ar,$en,$category,$price,$days,$payload]) {
            DB::table('store_items')->updateOrInsert(['key'=>$key],[
                'name'=>json_encode(['ar'=>$ar,'en'=>$en], JSON_UNESCAPED_UNICODE),
                'category'=>$category,
                'price'=>$price,
                'duration_days'=>$days,
                'payload'=>json_encode($payload, JSON_UNESCAPED_UNICODE),
                'active'=>true,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_items')) return;
        DB::table('store_items')->whereIn('key', [
            'badge_fairplay','cover_phoenix','cover_ocean','cover_neon','cover_forest','cover_sunset',
            'cover_ice','cover_tiger','cover_eagle','cover_lava','cover_pearl',
        ])->delete();
    }
};
