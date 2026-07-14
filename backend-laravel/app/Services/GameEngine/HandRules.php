<?php

namespace App\Services\GameEngine;

class HandRules extends AbstractCardRules
{
    public function initialState(array $players, array $options = []): array
    {
        $players = array_values($players);
        $deck = DeckFactory::pinochle();
        $cardsEach = min(14, intdiv(max(1, count($deck) - 1), max(1, count($players))));
        [$hands, $deck] = $this->deal($players, $deck, $cardsEach);
        $discard = [];
        if ($deck) {
            $discard[] = array_shift($deck)->id();
        }

        $previousScores = $options['previous_scores'] ?? array_fill_keys($players, 0);
        $round = (int) ($options['round'] ?? 1);

        return [
            'phase' => 'playing',
            'game_type' => 'hand',
            'players' => $players,
            'turn' => $players[0] ?? null,
            'hands' => $hands,
            'deck' => array_map(fn ($card) => $card->id(), $deck),
            'discard' => $discard,
            'melds' => [],
            'first_meld_done' => [],
            'drew_this_turn' => [],
            'scores' => $previousScores,
            'round' => $round,
            'rounds_total' => 5,
            'target' => (int) ($options['target'] ?? 5),
            'messages' => [
                'هاند: الجولة '.$round.' من 5. اسحب من الدك أو الرمي، ثم نزّل مجموعات أو سلاسل من 3 أوراق فأكثر. مجموع النزول الأول يجب أن يبلغ 51 نقطة.',
            ],
        ];
    }

    public function validate(array $state, string $playerId, string $action, array $payload): bool
    {
        if ($action === 'new_round') {
            return ($state['phase'] ?? null) === 'finished' && !empty($state['next_round_available']);
        }

        if (($state['turn'] ?? null) !== $playerId || ($state['phase'] ?? 'playing') !== 'playing') {
            return false;
        }

        if (in_array($action, ['draw_deck', 'draw_discard'], true)) {
            if (!empty($state['drew_this_turn'][$playerId])) {
                return false;
            }
            return $action === 'draw_deck'
                ? !empty($state['deck']) || !empty($state['discard'])
                : !empty($state['discard']);
        }

        if ($action === 'meld') {
            $cards = array_values($payload['cards'] ?? []);
            return count($cards) >= 3
                && count($cards) <= 13
                && $this->hasCards($state['hands'][$playerId] ?? [], $cards)
                && $this->isValidMeld($cards);
        }

        if ($action === 'arrange_melds') {
            return $this->validateArrange($state, $playerId, $payload);
        }

        if ($action === 'layoff') {
            return $this->validateLayoff($state, $playerId, $payload);
        }

        if ($action === 'discard') {
            return !empty($state['drew_this_turn'][$playerId])
                && in_array((string) ($payload['card'] ?? ''), $state['hands'][$playerId] ?? [], true);
        }

        return false;
    }

    public function apply(array $state, string $playerId, string $action, array $payload): array
    {
        if (!$this->validate($state, $playerId, $action, $payload)) {
            $state['last_error'] = 'invalid_action';
            $state['last_error_message'] = 'الحركة غير مقبولة الآن. اسحب أولًا، ثم نزّل مجموعة أو سلسلة صحيحة، وبعدها ارمِ ورقة.';
            return $state;
        }

        unset($state['last_error'], $state['last_error_message']);

        if ($action === 'new_round') {
            return $this->initialState($state['players'] ?? [], [
                'previous_scores' => $state['scores'] ?? [],
                'round' => (int) ($state['round'] ?? 1) + 1,
                'target' => $state['target'] ?? 5,
            ]);
        }

        if ($action === 'draw_deck') {
            $this->restoreDeckIfNeeded($state);
            if (!empty($state['deck'])) {
                $state['hands'][$playerId][] = array_shift($state['deck']);
            }
            $state['hands'][$playerId] = $this->sortHand($state['hands'][$playerId]);
            $state['drew_this_turn'][$playerId] = true;
            $state['messages'][] = $this->labelPlayer($playerId).' سحب من الدك.';
        }

        if ($action === 'draw_discard') {
            $state['hands'][$playerId][] = array_pop($state['discard']);
            $state['hands'][$playerId] = $this->sortHand($state['hands'][$playerId]);
            $state['drew_this_turn'][$playerId] = true;
            $state['messages'][] = $this->labelPlayer($playerId).' سحب من الرمي.';
        }

        if ($action === 'meld') {
            $cards = array_values($payload['cards']);
            $required = $this->openingRequirement();
            $points = $this->points($cards);
            if (empty($state['first_meld_done'][$playerId]) && $points < $required) {
                $state['last_error'] = 'opening_requirement_not_met';
                $state['last_error_message'] = 'مجموع النزول الأول يجب أن يبلغ '.$required.' نقطة على الأقل.';
                return $state;
            }

            $this->removeCards($state['hands'][$playerId], $cards);
            $state['melds'][$playerId][] = $cards;
            $state['first_meld_done'][$playerId] = true;
            $state['messages'][] = $this->labelPlayer($playerId).' نزّل مجموعة بقيمة '.$points.'.';
            if (empty($state['hands'][$playerId])) {
                return $this->finishRound($state, $playerId, false);
            }
        }

        if ($action === 'arrange_melds') {
            $groups = array_values($payload['groups'] ?? []);
            $allCards = [];
            foreach ($groups as $group) {
                foreach ($group as $card) {
                    $allCards[] = $card;
                }
            }
            $openingPoints = $this->points($allCards);
            if (empty($state['first_meld_done'][$playerId]) && $openingPoints < $this->openingRequirement()) {
                $state['last_error'] = 'opening_requirement_not_met';
                $state['last_error_message'] = 'مجموع كل مجموعات النزول الأول يجب أن يبلغ '.$this->openingRequirement().' نقطة على الأقل.';
                return $state;
            }

            $this->removeCards($state['hands'][$playerId], $allCards);
            foreach ($groups as $group) {
                $state['melds'][$playerId][] = array_values($group);
            }
            $state['table_groups'][$playerId] = $state['melds'][$playerId];
            $state['first_meld_done'][$playerId] = true;
            $state['messages'][] = $this->labelPlayer($playerId).' نزّل '.count($groups).' مجموعات بقيمة إجمالية '.$openingPoints.'.';
            if (empty($state['hands'][$playerId])) {
                return $this->finishRound($state, $playerId, true);
            }
        }

        if ($action === 'layoff') {
            $owner = (string) ($payload['group_owner'] ?? $playerId);
            $index = (int) ($payload['group_index'] ?? -1);
            $cards = array_values($payload['cards'] ?? []);
            $combined = array_merge($state['melds'][$owner][$index], $cards);
            $this->removeCards($state['hands'][$playerId], $cards);
            $state['melds'][$owner][$index] = $combined;
            $state['table_groups'][$owner] = $state['melds'][$owner];
            $state['messages'][] = $this->labelPlayer($playerId).' ركّب '.count($cards).' ورقة على مجموعة '.($owner === $playerId ? 'خاصة به.' : 'شريكه.');
            if (empty($state['hands'][$playerId])) {
                return $this->finishRound($state, $playerId, true);
            }
        }

        if ($action === 'discard') {
            $card = (string) $payload['card'];
            $this->removeCard($state['hands'][$playerId], $card);
            $state['discard'][] = $card;
            $state['turn'] = $this->playerKeyNext($state['players'], $playerId);
            unset($state['drew_this_turn'][$playerId]);
            $state['messages'][] = $this->labelPlayer($playerId).' رمى ورقة.';

            if (empty($state['hands'][$playerId])) {
                return $this->finishRound($state, $playerId, false);
            }
        }

        $state['hands'][$playerId] = $this->sortHand($state['hands'][$playerId] ?? []);
        return $state;
    }

    protected function openingRequirement(): int|float
    {
        return 51;
    }

    protected function validateArrange(array $state, string $playerId, array $payload): bool
    {
        $groups = array_values($payload['groups'] ?? []);
        if (empty($groups)) {
            return false;
        }

        $all = [];
        foreach ($groups as $group) {
            if (!is_array($group) || count($group) < 3 || count($group) > 13 || !$this->isValidMeld($group)) {
                return false;
            }
            foreach ($group as $card) {
                $all[] = $card;
            }
        }

        if (!$this->hasCards($state['hands'][$playerId] ?? [], $all)) {
            return false;
        }

        return !empty($state['first_meld_done'][$playerId]) || $this->points($all) >= $this->openingRequirement();
    }

    protected function validateLayoff(array $state, string $playerId, array $payload): bool
    {
        if (empty($state['first_meld_done'][$playerId])) {
            return false;
        }

        $owner = (string) ($payload['group_owner'] ?? $playerId);
        $index = (int) ($payload['group_index'] ?? -1);
        $cards = array_values($payload['cards'] ?? []);
        if (empty($cards) || !$this->hasCards($state['hands'][$playerId] ?? [], $cards)) {
            return false;
        }
        if (!isset($state['melds'][$owner][$index]) || !$this->canAttachToOwner($state, $playerId, $owner)) {
            return false;
        }

        return $this->isValidMeld(array_merge($state['melds'][$owner][$index], $cards));
    }

    protected function canAttachToOwner(array $state, string $playerId, string $owner): bool
    {
        if ($owner === $playerId) {
            return true;
        }
        if (empty($state['teams'])) {
            return false;
        }
        return $this->teamOf($state, $playerId) === $this->teamOf($state, $owner);
    }

    protected function hasCards(array $hand, array $cards): bool
    {
        $available = array_count_values($hand);
        foreach ($cards as $card) {
            if (($available[$card] ?? 0) < 1) {
                return false;
            }
            $available[$card]--;
        }
        return true;
    }

    protected function removeCards(array &$hand, array $cards): void
    {
        foreach ($cards as $card) {
            $this->removeCard($hand, (string) $card);
        }
    }

    protected function points(array $cards): int|float
    {
        $sum = 0;
        foreach ($cards as $card) {
            $rank = $this->rank($card);
            $sum += [
                'A' => 15,
                'K' => 10,
                'Q' => 10,
                'J' => 10,
                '10' => 10,
                '9' => 5,
                '8' => 5,
                '7' => 5,
                '6' => 5,
                '5' => 5,
                '4' => 5,
                '3' => 5,
                '2' => 20,
                'JOKER' => 25,
            ][$rank] ?? 0;
        }
        return $sum;
    }

    protected function isValidMeld(array $cards): bool
    {
        if (count($cards) < 3 || count($cards) > 13) {
            return false;
        }

        $wildCount = count(array_filter($cards, fn ($card) => $this->rank($card) === 'JOKER'));
        $naturalCards = array_values(array_filter($cards, fn ($card) => $this->rank($card) !== 'JOKER'));
        if (empty($naturalCards)) {
            return false;
        }

        $ranks = array_map(fn ($card) => $this->rank($card), $naturalCards);
        if (count(array_unique($ranks)) === 1) {
            return true;
        }

        $suits = array_map(fn ($card) => $this->suit($card), $naturalCards);
        if (count(array_unique($suits)) !== 1) {
            return false;
        }

        $values = array_map(fn ($card) => $this->cardValue($card), $naturalCards);
        if (count(array_unique($values)) !== count($values)) {
            return false;
        }
        sort($values);
        $missing = 0;
        for ($i = 1; $i < count($values); $i++) {
            $missing += $values[$i] - $values[$i - 1] - 1;
        }

        return $missing <= $wildCount && (max($values) - min($values) + 1 + ($wildCount - $missing)) <= 13;
    }

    protected function finishRound(array $state, string $winnerId, bool $wentOutByMeld): array
    {
        $fullHand = $wentOutByMeld && empty($state['melds'][$winnerId] ?? []);
        $bonus = $fullHand ? -60 : -30;
        $state['scores'][$winnerId] = (float) ($state['scores'][$winnerId] ?? 0) + $bonus;
        foreach ($state['players'] as $player) {
            if ($player !== $winnerId) {
                $state['scores'][$player] = (float) ($state['scores'][$player] ?? 0) + $this->points($state['hands'][$player] ?? []);
            }
        }

        $state['winner'] = $winnerId;
        $state['round_result'] = ['winner' => $winnerId, 'bonus' => $bonus, 'full_hand' => $fullHand];
        $state['messages'][] = 'انتهت الجولة '.($state['round'] ?? 1).'. الفائز: '.$this->labelPlayer($winnerId).' وحصل على '.$bonus.' نقطة.';

        if ((int) ($state['round'] ?? 1) >= (int) ($state['rounds_total'] ?? 5)) {
            $state['phase'] = 'finished';
            $state['game_over'] = true;
            $state['overall_winner'] = $this->lowestScorePlayer($state['scores'] ?? []);
            $state['messages'][] = 'انتهت لعبة الهاند. الفائز النهائي هو أقل لاعب بالنقاط: '.$this->labelPlayer((string) $state['overall_winner']);
        } else {
            $state['phase'] = 'finished';
            $state['next_round_available'] = true;
            $state['messages'][] = 'انتهت الجولة. ابدأ الجولة التالية لاستكمال المباراة.';
        }

        return $state;
    }

    protected function restoreDeckIfNeeded(array &$state): void
    {
        if (!empty($state['deck']) || count($state['discard'] ?? []) <= 1) {
            return;
        }
        $top = array_pop($state['discard']);
        $recycled = $state['discard'];
        for ($i = count($recycled) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$recycled[$i], $recycled[$j]] = [$recycled[$j], $recycled[$i]];
        }
        $state['deck'] = $recycled;
        $state['discard'] = [$top];
        $state['messages'][] = 'تم خلط كومة الرمي وإعادتها إلى الدك.';
    }

    private function lowestScorePlayer(array $scores): ?string
    {
        if (empty($scores)) {
            return null;
        }
        asort($scores);
        return array_key_first($scores);
    }
}
