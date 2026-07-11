<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WarqnaPro\PlayActionNormalizer;
use App\Services\GameEngine\TarneebRules;

class V126CardNormalizerTest extends TestCase
{
    public function test_normalizer_accepts_many_correct_card_formats_without_recursive_replacement(): void
    {
        $n=new PlayActionNormalizer();
        $state=['hands'=>['user:1'=>['A_clubs','10_hearts','K_spades','Q_diamonds']]];

        foreach([
            ['card'=>'A_clubs'],
            ['card_id'=>'A♣'],
            ['id'=>'A سنك'],
            ['rank'=>'A','suit'=>'clubs'],
            ['rank'=>'A','suit'=>'♣'],
            ['card'=>['id'=>'A_clubs']],
            ['card'=>['rank'=>'A','suit'=>'سنك']],
            ['card'=>'clubs_A'],
            ['card'=>'♣A'],
        ] as $payload){
            [$action,$p]=$n->normalize('play_card',$payload,$state,'user:1');
            $this->assertSame('play_card',$action);
            $this->assertSame('A_clubs',$p['card']);
        }
        $this->assertSame('A_clubs',$n->normalizeCardId('A_clubs'));
    }

    public function test_tarneeb_accepts_legacy_room_state_and_localized_card_formats(): void
    {
        $engine=new TarneebRules();
        $state=$this->legacyState();
        $this->assertTrue($engine->validate($state,'user:1','play_card',['card_id'=>'A♣']));
        $this->assertTrue($engine->validate($state,'user:1','play_card',['rank'=>'A','suit'=>'سنك']));
        $this->assertTrue($engine->validate($state,'user:1','play_card',['card'=>['rank'=>'A','suit'=>'clubs']]));
    }

    public function test_tarneeb_follow_suit_is_still_enforced_for_legacy_room_state(): void
    {
        $engine=new TarneebRules();
        $state=$this->legacyState();
        $state['trick']=['user:2'=>'K_hearts'];
        $this->assertFalse($engine->validate($state,'user:1','play_card',['card'=>'A_clubs']));
        $this->assertTrue($engine->validate($state,'user:1','play_card',['card'=>'10_hearts']));
    }

    private function legacyState(): array
    {
        return [
            'phase'=>'playing','turn'=>'user:1','players'=>['user:1','user:2','user:3','user:4'],
            'hands'=>['user:1'=>['A_clubs','10_hearts'],'user:2'=>[],'user:3'=>[],'user:4'=>[]],
            'trick'=>[],'trump'=>'hearts',
            'teams'=>['teamA'=>['user:1','user:3'],'teamB'=>['user:2','user:4']],
            'round_tricks'=>['teamA'=>0,'teamB'=>0],'score'=>['teamA'=>0,'teamB'=>0],
            'bid'=>['player'=>'user:1','value'=>7,'team'=>'teamA'],
        ];
    }
}
