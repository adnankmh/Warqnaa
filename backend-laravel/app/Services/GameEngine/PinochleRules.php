<?php

namespace App\Services\GameEngine;

class PinochleRules extends HandRules
{
    public function initialState(array $players, array $options = []): array
    {
        $players = array_values($players);
        $deck = DeckFactory::pinochle();
        $cardsEach = min(18, intdiv(max(1, count($deck) - 1), max(1, count($players))));
        [$hands, $deck] = $this->deal($players, $deck, $cardsEach);
        $starter = $players[0] ?? null;
        if ($starter !== null && $deck) {
            $hands[$starter][] = array_shift($deck)->id();
            $hands[$starter] = $this->sortHand($hands[$starter]);
        }

        $previousScores = $options['previous_scores'] ?? array_fill_keys($players, 0);
        $target = (int) ($options['target'] ?? (count($players) === 2 ? 150 : 222));

        return [
            'phase' => 'playing',
            'game_type' => 'banakil',
            'players' => $players,
            'teams' => $this->teams($players),
            'turn' => $starter,
            'hands' => $hands,
            'deck' => array_map(fn ($card) => $card->id(), $deck),
            'discard' => [],
            'melds' => [],
            'first_meld_done' => [],
            // The starter has 19 cards and begins by discarding without drawing.
            'drew_this_turn' => $starter === null ? [] : [$starter => true],
            'scores' => $previousScores,
            'round' => (int) ($options['round'] ?? 1),
            'target' => $target,
            'twos_wild' => true,
            'jokers_wild' => true,
            'messages' => [
                'بناكل: 18 ورقة لكل لاعب و19 للاعب البادئ. يبدأ برمي ورقة، ولا يوجد حد أدنى للنزول. يمكن تركيب الأوراق على مجموعات الشريك فقط.',
            ],
        ];
    }

    protected function openingRequirement(): int|float
    {
        return 0;
    }

    protected function points(array $cards): int|float
    {
        $sum = 0.0;
        foreach ($cards as $card) {
            $rank = $this->rank($card);
            if ($rank === 'JOKER') {
                $sum += 4;
            } elseif ($rank === '2') {
                $sum += 2;
            } elseif (in_array($rank, ['3', '4', '5', '6'], true)) {
                $sum += 0.5;
            } else {
                $sum += 1;
            }
        }
        return $sum;
    }

    protected function isValidMeld(array $cards): bool
    {
        if (count($cards) < 3 || count($cards) > 13) {
            return false;
        }

        $jokerCount = 0;
        $twoCount = 0;
        $natural = [];
        foreach ($cards as $card) {
            $rank = $this->rank($card);
            if ($rank === 'JOKER') {
                $jokerCount++;
            } elseif ($rank === '2') {
                $twoCount++;
            } else {
                $natural[] = $card;
            }
        }

        // One joker and one 2 may coexist, but two of the same wildcard may not.
        if ($jokerCount > 1 || $twoCount > 1 || empty($natural)) {
            return false;
        }
        $wildCount = $jokerCount + $twoCount;

        $naturalRanks = array_map(fn ($card) => $this->rank($card), $natural);
        $naturalSuits = array_map(fn ($card) => $this->suit($card), $natural);

        // Banakil rank sets are valid only for 3s or Aces and use different suits.
        if (count(array_unique($naturalRanks)) === 1 && in_array($naturalRanks[0], ['3', 'A'], true)) {
            return count(array_unique($naturalSuits)) === count($naturalSuits)
                && count($natural) + $wildCount <= 4;
        }

        // Otherwise the meld must be a same-suit sequence; wildcards fill gaps or ends.
        if (count(array_unique($naturalSuits)) !== 1) {
            return false;
        }
        $values = array_map(fn ($card) => $this->cardValue($card), $natural);
        if (count(array_unique($values)) !== count($values)) {
            return false;
        }
        sort($values);
        $missing = 0;
        for ($i = 1; $i < count($values); $i++) {
            $missing += $values[$i] - $values[$i - 1] - 1;
        }

        return $missing <= $wildCount && (max($values) - min($values) + 1 + ($wildCount - $missing)) <= 12;
    }

    protected function finishRound(array $state, string $winnerId, bool $wentOutByMeld): array
    {
        $winnerTeam = $this->teamOf($state, $winnerId);
        $teams = $state['teams'] ?? [];
        $teamScores = [];

        foreach ($teams as $team => $members) {
            $laid = 0.0;
            $remaining = 0.0;
            foreach ($members as $member) {
                foreach ($state['melds'][$member] ?? [] as $meld) {
                    $laid += $this->points($meld);
                }
                $remaining += $this->points($state['hands'][$member] ?? []);
            }
            $teamScores[$team] = $laid - $remaining + ($team === $winnerTeam ? 20 : 0);
        }

        $opponentLaidAny = false;
        foreach ($teams as $team => $members) {
            if ($team === $winnerTeam) {
                continue;
            }
            foreach ($members as $member) {
                if (!empty($state['melds'][$member])) {
                    $opponentLaidAny = true;
                    break 2;
                }
            }
        }

        $winnerMeldCount = 0;
        foreach ($teams[$winnerTeam] ?? [$winnerId] as $member) {
            $winnerMeldCount += count($state['melds'][$member] ?? []);
        }
        $allAtOnce = $wentOutByMeld && $winnerMeldCount === 1;
        if ($allAtOnce) {
            $teamScores[$winnerTeam] = $opponentLaidAny ? 51 : 102;
            foreach (array_keys($teamScores) as $team) {
                if ($team !== $winnerTeam) {
                    $teamScores[$team] = 0;
                }
            }
        }

        foreach ($teams as $team => $members) {
            foreach ($members as $member) {
                $state['scores'][$member] = (float) ($state['scores'][$member] ?? 0) + ($teamScores[$team] ?? 0);
            }
        }

        $state['winner'] = $winnerId;
        $state['winner_team'] = $winnerTeam;
        $state['round_result'] = ['winner' => $winnerId, 'team_scores' => $teamScores, 'all_at_once' => $allAtOnce];
        $state['phase'] = 'finished';
        $state['messages'][] = 'انتهت جولة البناكل. نتيجة الفريق الفائز في الجولة: '.($teamScores[$winnerTeam] ?? 0).' نقطة.';

        $winnerTotal = 0.0;
        foreach ($teams[$winnerTeam] ?? [$winnerId] as $member) {
            $winnerTotal = max($winnerTotal, (float) ($state['scores'][$member] ?? 0));
        }
        if ($winnerTotal >= (float) ($state['target'] ?? 222)) {
            $state['game_over'] = true;
            $state['overall_winner_team'] = $winnerTeam;
            $state['messages'][] = 'انتهت المباراة بوصول الفريق إلى الهدف '.($state['target'] ?? 222).' نقطة.';
        } else {
            $state['next_round_available'] = true;
            $state['messages'][] = 'يمكن بدء جولة بناكل جديدة.';
        }

        return $state;
    }
}
