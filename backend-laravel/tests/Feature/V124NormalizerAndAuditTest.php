<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WarqnaPro\PlayActionNormalizer;

class V124NormalizerAndAuditTest extends TestCase
{
    public function test_action_normalizer_converts_rank_and_suit_to_hand_card(): void
    {
        $n=new PlayActionNormalizer();
        $state=['hands'=>['user:1'=>['A_clubs','K_hearts']]];
        [$action,$payload]=$n->normalize('play_card',['rank'=>'A','suit'=>'♣'],$state,'user:1');
        $this->assertSame('play_card',$action);
        $this->assertSame('A_clubs',$payload['card']);
    }

    public function test_action_normalizer_converts_bid_shortcut(): void
    {
        $n=new PlayActionNormalizer();
        [$action,$payload]=$n->normalize('bid:9',[],[], 'user:1');
        $this->assertSame('bid',$action);
        $this->assertSame(9,$payload['value']);
    }
}
