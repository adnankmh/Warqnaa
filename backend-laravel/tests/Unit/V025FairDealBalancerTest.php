<?php

namespace Tests\Unit;

use App\Services\GameEngine\FairDealBalancer;
use PHPUnit\Framework\TestCase;

class V025FairDealBalancerTest extends TestCase
{
    public function test_balancer_preserves_unique_deck_and_hand_sizes_while_avoiding_a_premiumless_hand(): void
    {
        $hands = [
            ['2_C','3_C','4_C','5_C','6_C','7_C','8_C','9_C','10_C','2_D','3_D','4_D','5_D'],
            ['A_C','K_C','Q_C','J_C','A_D','K_D','Q_D','J_D','A_H','K_H','Q_H','J_H','10_H'],
            ['6_D','7_D','8_D','9_D','10_D','2_H','3_H','4_H','5_H','6_H','7_H','8_H','9_H'],
            ['A_S','K_S','Q_S','J_S','10_S','9_S','8_S','7_S','6_S','5_S','4_S','3_S','2_S'],
        ];

        $balanced = FairDealBalancer::balance($hands, 'trick');
        $before = array_merge(...$hands);
        $after = array_merge(...$balanced);
        sort($before);
        sort($after);

        $this->assertSame($before, $after);
        $this->assertCount(52, array_unique($after));
        foreach ($balanced as $hand) {
            $this->assertCount(13, $hand);
            $premium = array_filter($hand, fn (string $card) => (bool) preg_match('/^(A|K|Q|J)[_-]/', $card));
            $this->assertGreaterThanOrEqual(2, count($premium));
        }
    }
}
