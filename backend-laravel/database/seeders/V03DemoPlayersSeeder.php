<?php

namespace Database\Seeders;

use App\Models\{Club, ClubMember, Profile, User, Wallet};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class V03DemoPlayersSeeder extends Seeder
{
    public function run(): void
    {
        $players = [
            ['Jinan', 3, 'PS', '🌙'], ['Kanan', 8, 'JO', '🦅'], ['Hoor', 15, 'EG', '🌸'],
            ['Raad', 22, 'IQ', '⚡'], ['Shahd', 31, 'SA', '✨'], ['Hossam', 44, 'PS', '🛡️'],
            ['Afnan', 57, 'AE', '💎'], ['Mutasim', 68, 'TR', '🐺'], ['Qamar', 79, 'MA', '🌕'],
            ['Bayan', 91, 'LB', '👑'],
        ];
        $created = [];
        foreach ($players as [$name, $level, $country, $avatar]) {
            $email = strtolower($name).'@warqna.demo';
            $user = User::updateOrCreate(
                ['email'=>$email],
                ['username'=>$name, 'password'=>Hash::make('Warqna123!'), 'is_admin'=>false, 'is_banned'=>false]
            );
            Profile::updateOrCreate(['user_id'=>$user->id], [
                'display_name'=>$name, 'avatar'=>$avatar, 'country_code'=>$country,
                'country_name'=>country_name($country), 'level'=>$level, 'xp'=>$level * $level * 950,
                'games_played'=>$level * 18, 'wins'=>(int) round($level * 9.5),
                'name_color'=>'#facc15', 'chat_color'=>'#ffffff', 'pasha_days'=>$level >= 50 ? 2 : 0,
                'badge'=>$level >= 75 ? 'legend' : ($level >= 35 ? 'pro' : 'beginner'),
            ]);
            Wallet::updateOrCreate(['user_id'=>$user->id], ['tokens'=>max(1000, $level * 4500), 'gems'=>0]);
            $created[] = $user;
        }

        if (count($created) < 4) return;
        $owner = $created[array_key_last($created)];
        $club = Club::updateOrCreate(['name'=>'Warqna Global Club'], [
            'owner_id'=>$owner->id, 'level'=>12, 'weekly_points'=>42000, 'total_points'=>850000,
            'treasury'=>125000, 'capacity'=>50, 'league_tier'=>'gold', 'visibility'=>'public',
            'logo'=>'🦁', 'description'=>'مجموعة تجريبية متعددة المستويات لاختبار المنافسات والتحديات والصلاحيات.',
        ]);
        foreach ($created as $index=>$user) {
            $role = $user->is($owner) ? 'owner' : ($index % 3 === 0 ? 'moderator' : 'member');
            $permissions = $role === 'owner'
                ? ['all'=>true]
                : ($role === 'moderator' ? ['manage_chat'=>true, 'create_tournaments'=>true, 'accept_members'=>true] : []);
            ClubMember::updateOrCreate(['club_id'=>$club->id, 'user_id'=>$user->id], [
                'role'=>$role, 'permissions'=>$permissions, 'weekly_points'=>($index + 1) * 275,
            ]);
        }
    }
}
