<?php

namespace Database\Seeders;

use App\Models\{Profile,User,Wallet};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPlayersV025Seeder extends Seeder
{
    public function run(): void
    {
        if (!filter_var(env('WARQNA_SEED_DEMO_PLAYERS', !app()->environment('production')), FILTER_VALIDATE_BOOL)) return;

        $players = [
            ['Bayan','بيان',8,18000,'🦋'], ['Kenan','كنان',12,26000,'🦅'], ['Jameel','جميل',18,42000,'🐯'],
            ['Raad','رعد',24,65000,'⚡'], ['Asem','عاصم',31,90000,'🛡️'], ['Moatasem','معتصم',38,130000,'🦁'],
            ['Hossam','حسام',45,190000,'🐺'], ['Janan','جنان',15,33000,'🌸'], ['Hoor','حور',22,56000,'🌙'],
            ['Jannat','جنات',29,81000,'🌿'], ['Alaa','آلاء',36,115000,'💎'], ['Afnan','أفنان',43,170000,'🌹'],
            ['Shahd','شهد',50,240000,'👑'], ['Hala','حلا',58,320000,'⭐'], ['Shatha','شذى',66,430000,'🪷'],
            ['Qamar','قمر',75,600000,'🌕'],
        ];

        foreach ($players as [$username,$display,$level,$tokens,$avatar]) {
            $email = strtolower($username).'@warqna.local';
            $user = User::updateOrCreate(['email'=>$email], [
                'username'=>$username,
                'password'=>Hash::make('Warqna025!'),
                'is_admin'=>false,
                'is_banned'=>false,
                'email_verified_at'=>now(),
            ]);
            Profile::updateOrCreate(['user_id'=>$user->id], [
                'display_name'=>$display,'avatar'=>$avatar,'country_code'=>'PS','country_name'=>country_name('PS'),
                'level'=>$level,'xp'=>$level*$level*900,'games_played'=>$level*18,'wins'=>$level*9,
                'name_color'=>'#facc15','chat_color'=>'#ffffff','pasha_days'=>$level >= 50 ? 3 : 0,'badge'=>$level >= 50 ? 'legendary' : 'pro',
            ]);
            Wallet::updateOrCreate(['user_id'=>$user->id], ['tokens'=>$tokens,'gems'=>0]);
        }
    }
}
