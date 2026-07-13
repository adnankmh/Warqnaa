<?php

declare(strict_types=1);

namespace App\Services\GameEngine\TarneebStandalone;

/**
 * Standalone, server-authoritative Tarneeb engine.
 *
 * Original implementation inspired by standard Tarneeb gameplay flow:
 * deal -> bidding -> choose trump -> tricks -> score -> next round/game end.
 * It is framework-free and can be used from Laravel, plain PHP, REST APIs,
 * WebSocket servers, or CLI bots.
 */
final class TarneebException extends \RuntimeException
{
    public string $codeKey;
    /** @var array<string,mixed> */
    public array $context;

    /** @param array<string,mixed> $context */
    public function __construct(string $message, string $codeKey = 'error', array $context = [])
    {
        parent::__construct($message);
        $this->codeKey = $codeKey;
        $this->context = $context;
    }
}

final class TarneebEngine
{
    public const SUITS = ['C', 'D', 'S', 'H']; // Clubs, Diamonds, Spades, Hearts.
    public const RANKS = ['A', 'K', 'Q', 'J', '10', '9', '8', '7', '6', '5', '4', '3', '2'];

    /** @var array<string,string> */
    public const SUIT_AR = [
        'C' => 'سنك / شجرة',
        'D' => 'ديناري',
        'S' => 'بستوني',
        'H' => 'كبة',
    ];

    /** @var array<string,string> */
    public const SUIT_EN = [
        'C' => 'Clubs',
        'D' => 'Diamonds',
        'S' => 'Spades',
        'H' => 'Hearts',
    ];

    /** @var array<string,int> */
    private const RANK_POWER = [
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        '10' => 10,
        'J' => 11,
        'Q' => 12,
        'K' => 13,
        'A' => 14,
    ];

    /** @var array<string,int> */
    private const SORT_SUIT_ORDER = [
        'C' => 0, // سنك / شجرة
        'D' => 1, // ديناري
        'S' => 2, // بستوني
        'H' => 3, // كبة
    ];

    /** @var array<string,mixed> */
    private array $defaultRules = [
        'targetScore' => 41,
        'allowedTargetScores' => [31, 41, 61],
        'minBid' => 7,
        'maxBid' => 13,
        'playersCount' => 4,
        'cardsPerPlayer' => 13,
        'teams' => [0 => 0, 1 => 1, 2 => 0, 3 => 1],
        'firstBidder' => 'after_dealer', // after_dealer only in this engine.
        'firstTrickLeader' => 'bid_winner', // bid_winner or after_dealer.
        'redealOnAllPass' => true,
        'advanceDealerEachRound' => true,
        'sortHands' => true,
        'scoreMode' => 'standard',
        'bonusForThirteenBidAndMakeAll' => 0,
        'allowBotForAway' => true,
        'maxMissedTurnsBeforeAway' => 3,
        'turnSeconds' => 8,
        'locale' => 'ar',
    ];

    /**
     * Create a new Tarneeb game.
     *
     * Player format:
     * [
     *   ['id'=>'u1', 'name'=>'أحمد', 'bot'=>false],
     *   ['id'=>'bot1', 'name'=>'Bot 1', 'bot'=>true],
     *   ... exactly 4 players ...
     * ]
     *
     * @param array<int,array<string,mixed>> $players
     * @param array<string,mixed> $rules
     * @return array<string,mixed>
     */
    public function newGame(array $players, ?int $seed = null, array $rules = []): array
    {
        $rules = $this->normalizeRules(array_replace($this->defaultRules, $rules));
        $this->assertPlayers($players);
        $seed = $seed ?? random_int(1, PHP_INT_MAX);

        $state = [
            'id' => $this->newId('trn'),
            'engine' => 'warqna-tarneeb-standalone',
            'version' => '1.1.0',
            'phase' => 'new',
            'round' => 0,
            'seed' => $seed,
            'rules' => $rules,
            'players' => [],
            'dealerSeat' => 3,
            'currentSeat' => 0,
            'hands' => [0 => [], 1 => [], 2 => [], 3 => []],
            'scores' => [0 => 0, 1 => 0],
            'roundTricks' => [0 => 0, 1 => 0],
            'bid' => null,
            'trump' => null,
            'trick' => [],
            'completedTricks' => [],
            'winnerTeam' => null,
            'events' => [],
            'replay' => [],
            'security' => [
                'lastActionNo' => 0,
                'stateHash' => null,
            ],
            'createdAt' => time(),
            'updatedAt' => time(),
        ];

        foreach ($players as $seat => $p) {
            $state['players'][$seat] = [
                'seat' => $seat,
                'id' => (string)$p['id'],
                'name' => (string)($p['name'] ?? ('Player ' . ($seat + 1))),
                'bot' => (bool)($p['bot'] ?? false),
                'team' => (int)$rules['teams'][$seat],
                'connected' => true,
                'away' => false,
                'missedTurns' => 0,
                'leftCount' => 0,
                'bannedFromGame' => false,
            ];
        }

        return $this->startNextRound($state, 'new_game');
    }

    /**
     * Convenience helper to create a game with one of the official target scores: 31, 41, or 61.
     *
     * @param array<int,array<string,mixed>> $players
     * @param array<string,mixed> $rules
     * @return array<string,mixed>
     */
    public function newGameWithTarget(array $players, int $targetScore = 41, ?int $seed = null, array $rules = []): array
    {
        $rules['targetScore'] = $targetScore;
        return $this->newGame($players, $seed, $rules);
    }

    /**
     * @return array<int,int>
     */
    public function targetScoreOptions(): array
    {
        return [31, 41, 61];
    }

    /** @param array<string,mixed> $state */
    public function exportJson(array $state): string
    {
        return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
    }

    /** @return array<string,mixed> */
    public function importJson(string $json): array
    {
        $state = json_decode($json, true);
        if (!is_array($state)) {
            throw new TarneebException('ملف الحالة غير صالح.', 'invalid_state_json');
        }
        return $state;
    }

    /**
     * Public-safe view for one player: hides other players' cards.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function playerView(array $state, string $playerId): array
    {
        $seat = $this->seatOfPlayer($state, $playerId);
        $view = $state;
        $view['you'] = $seat;
        foreach ($view['hands'] as $s => $cards) {
            $s = (int)$s;
            if ($s !== $seat) {
                $view['hands'][$s] = [
                    'count' => count((array)$cards),
                    'hidden' => true,
                ];
            }
        }
        $view['legalCards'] = $state['phase'] === 'playing' && (int)$state['currentSeat'] === $seat
            ? $this->legalCards($state, $seat)
            : [];
        $view['canAct'] = (int)$state['currentSeat'] === $seat;
        $view['stateHash'] = $this->stateHash($state, false);
        return $view;
    }

    /** @param array<string,mixed> $state */
    public function publicView(array $state): array
    {
        $view = $state;
        foreach ($view['hands'] as $s => $cards) {
            $view['hands'][$s] = [
                'count' => count((array)$cards),
                'hidden' => true,
            ];
        }
        $view['stateHash'] = $this->stateHash($state, false);
        return $view;
    }

    /**
     * Bidding action. Use null for pass.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function bid(array $state, string $playerId, ?int $amount): array
    {
        $this->assertPhase($state, 'bidding');
        $seat = $this->seatOfPlayer($state, $playerId);
        $this->assertCurrentSeat($state, $seat);
        $this->assertPlayerCanAct($state, $seat);

        if (!isset($state['bid']) || !is_array($state['bid'])) {
            throw new TarneebException('حالة الطلب غير صالحة.', 'invalid_bid_state');
        }

        $bid = $state['bid'];
        $rules = $state['rules'];

        if (!empty($bid['passed'][$seat])) {
            throw new TarneebException('لا يمكنك الطلب بعد التمرير.', 'already_passed');
        }

        if ($amount === null) {
            $bid['passed'][$seat] = true;
            $bid['history'][] = ['seat' => $seat, 'action' => 'pass', 'at' => time()];
            $state['bid'] = $bid;
            $state = $this->record($state, 'bid_pass', ['seat' => $seat]);
        } else {
            $min = (int)$rules['minBid'];
            $max = (int)$rules['maxBid'];
            $currentHigh = (int)($bid['amount'] ?? 0);
            if ($amount < $min || $amount > $max) {
                throw new TarneebException("الطلب يجب أن يكون بين {$min} و {$max}.", 'bid_out_of_range', ['min' => $min, 'max' => $max]);
            }
            if ($amount <= $currentHigh) {
                throw new TarneebException('يجب أن يكون طلبك أعلى من الطلب الحالي.', 'bid_must_be_higher', ['current' => $currentHigh]);
            }
            $bid['seat'] = $seat;
            $bid['team'] = $this->teamOfSeat($state, $seat);
            $bid['amount'] = $amount;
            $bid['history'][] = ['seat' => $seat, 'action' => 'bid', 'amount' => $amount, 'at' => time()];
            $state['bid'] = $bid;
            $state = $this->record($state, 'bid_raise', ['seat' => $seat, 'amount' => $amount]);
        }

        return $this->advanceBidding($state);
    }

    /**
     * Highest bidder chooses trump suit.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function chooseTrump(array $state, string $playerId, string $suit): array
    {
        $this->assertPhase($state, 'choose_trump');
        $seat = $this->seatOfPlayer($state, $playerId);
        $this->assertCurrentSeat($state, $seat);
        $this->assertPlayerCanAct($state, $seat);
        $suit = strtoupper($suit);
        if (!in_array($suit, self::SUITS, true)) {
            throw new TarneebException('نوع الطرنيب غير صحيح.', 'invalid_trump_suit', ['suit' => $suit]);
        }
        if ((int)$state['bid']['seat'] !== $seat) {
            throw new TarneebException('اختيار الطرنيب فقط لصاحب أعلى طلب.', 'only_bid_winner_can_choose_trump');
        }

        $state['trump'] = $suit;
        $state['phase'] = 'playing';
        $state['currentSeat'] = ($state['rules']['firstTrickLeader'] ?? 'bid_winner') === 'after_dealer'
            ? $this->nextSeat((int)$state['dealerSeat'])
            : $seat;
        $state = $this->record($state, 'trump_chosen', ['seat' => $seat, 'suit' => $suit]);
        return $this->touch($state);
    }

    /**
     * Play a card by id, e.g. A_C, 10_H.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function playCard(array $state, string $playerId, string $cardId): array
    {
        $this->assertPhase($state, 'playing');
        $seat = $this->seatOfPlayer($state, $playerId);
        $this->assertCurrentSeat($state, $seat);
        $this->assertPlayerCanAct($state, $seat);
        $cardId = $this->normalizeCardId($cardId);

        if (!$this->handHasCard($state, $seat, $cardId)) {
            throw new TarneebException('هذه الورقة ليست في يدك.', 'card_not_in_hand', ['card' => $cardId]);
        }

        $legal = $this->legalCards($state, $seat);
        if (!in_array($cardId, $legal, true)) {
            throw new TarneebException('يجب اتباع نوع الورق المفتوح إذا كان موجودًا في يدك.', 'must_follow_suit', [
                'card' => $cardId,
                'legalCards' => $legal,
            ]);
        }

        $state['hands'][$seat] = array_values(array_filter($state['hands'][$seat], static fn ($c) => $c !== $cardId));
        if (!empty($state['rules']['sortHands'])) {
            $state['hands'][$seat] = $this->sortCards($state['hands'][$seat]);
        }
        $state['trick'][] = [
            'seat' => $seat,
            'team' => $this->teamOfSeat($state, $seat),
            'card' => $cardId,
            'at' => time(),
        ];
        $state['players'][$seat]['missedTurns'] = 0;
        $state = $this->record($state, 'card_played', ['seat' => $seat, 'card' => $cardId]);

        if (count($state['trick']) < 4) {
            $state['currentSeat'] = $this->nextSeat($seat);
            return $this->touch($state);
        }

        $winnerSeat = $this->trickWinnerSeat($state['trick'], (string)$state['trump']);
        $winnerTeam = $this->teamOfSeat($state, $winnerSeat);
        $state['roundTricks'][$winnerTeam]++;
        $completed = [
            'trickNo' => count($state['completedTricks']) + 1,
            'cards' => $state['trick'],
            'winnerSeat' => $winnerSeat,
            'winnerTeam' => $winnerTeam,
        ];
        $state['completedTricks'][] = $completed;
        $state['trick'] = [];
        $state['currentSeat'] = $winnerSeat;
        $state = $this->record($state, 'trick_won', $completed);

        if ($this->roundIsDone($state)) {
            return $this->scoreRound($state);
        }

        return $this->touch($state);
    }

    /**
     * Mark player as away/present. Away players can be auto-played by bot policy.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function setAway(array $state, string $playerId, bool $away): array
    {
        $seat = $this->seatOfPlayer($state, $playerId);
        $state['players'][$seat]['away'] = $away;
        $state['players'][$seat]['connected'] = !$away;
        $state = $this->record($state, $away ? 'player_away' : 'player_back', ['seat' => $seat]);
        return $this->touch($state);
    }

    /**
     * Player leaves. After 3 leaves he is banned from rejoining this state.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function leave(array $state, string $playerId): array
    {
        $seat = $this->seatOfPlayer($state, $playerId);
        $state['players'][$seat]['connected'] = false;
        $state['players'][$seat]['away'] = true;
        $state['players'][$seat]['leftCount'] = (int)$state['players'][$seat]['leftCount'] + 1;
        if ((int)$state['players'][$seat]['leftCount'] >= 3) {
            $state['players'][$seat]['bannedFromGame'] = true;
        }
        $state = $this->record($state, 'player_left', ['seat' => $seat, 'leftCount' => $state['players'][$seat]['leftCount']]);
        return $this->touch($state);
    }

    /**
     * Reconnect player if not banned.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function rejoin(array $state, string $playerId): array
    {
        $seat = $this->seatOfPlayer($state, $playerId);
        if (!empty($state['players'][$seat]['bannedFromGame'])) {
            throw new TarneebException('لا يمكنك العودة لهذه اللعبة بعد الخروج المتكرر.', 'player_banned_from_game');
        }
        $state['players'][$seat]['connected'] = true;
        $state['players'][$seat]['away'] = false;
        $state = $this->record($state, 'player_rejoined', ['seat' => $seat]);
        return $this->touch($state);
    }


    /**
     * Start the next round after phase round_end. Use this after you show the
     * round summary to users. It throws if the game is already over.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function nextRound(array $state): array
    {
        if ($state['phase'] === 'game_over') {
            throw new TarneebException('انتهت اللعبة ولا يمكن بدء جولة جديدة.', 'game_already_over');
        }
        if ($state['phase'] !== 'round_end') {
            throw new TarneebException('لا يمكن بدء جولة جديدة قبل نهاية الجولة الحالية.', 'round_not_ended');
        }
        return $this->startNextRound($state, 'manual_next_round');
    }

    /**
     * Called by a timer when a player is late. It can auto-play if allowed.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function onTurnTimeout(array $state): array
    {
        if (!in_array($state['phase'], ['bidding', 'choose_trump', 'playing'], true)) {
            return $state;
        }
        $seat = (int)$state['currentSeat'];
        $state['players'][$seat]['missedTurns'] = (int)$state['players'][$seat]['missedTurns'] + 1;
        if ((int)$state['players'][$seat]['missedTurns'] >= (int)$state['rules']['maxMissedTurnsBeforeAway']) {
            $state['players'][$seat]['away'] = true;
            $state['players'][$seat]['connected'] = false;
        }
        $state = $this->record($state, 'turn_timeout', ['seat' => $seat, 'missedTurns' => $state['players'][$seat]['missedTurns']]);
        if (!empty($state['rules']['allowBotForAway'])) {
            return $this->botMove($state, true);
        }
        return $this->touch($state);
    }

    /**
     * Execute current bot/away move if current player is bot or away.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function botMove(array $state, bool $allowAway = true): array
    {
        if (!in_array($state['phase'], ['bidding', 'choose_trump', 'playing'], true)) {
            return $state;
        }
        $seat = (int)$state['currentSeat'];
        $p = $state['players'][$seat];
        $isBotTurn = !empty($p['bot']) || ($allowAway && !empty($p['away']));
        if (!$isBotTurn) {
            return $state;
        }

        $playerId = (string)$p['id'];
        if ($state['phase'] === 'bidding') {
            $suggestedBid = $this->botSuggestedBid($state, $seat);
            return $this->bid($state, $playerId, $suggestedBid);
        }
        if ($state['phase'] === 'choose_trump') {
            return $this->chooseTrump($state, $playerId, $this->botBestTrumpSuit($state, $seat));
        }
        if ($state['phase'] === 'playing') {
            return $this->playCard($state, $playerId, $this->botChooseCard($state, $seat));
        }
        return $state;
    }

    /**
     * Keep playing bot/away turns until human turn or end.
     * @param array<string,mixed> $state
     * @return array<string,mixed>
     */
    public function autoPlayBotsAndAway(array $state, int $maxMoves = 200): array
    {
        for ($i = 0; $i < $maxMoves; $i++) {
            if (!in_array($state['phase'], ['bidding', 'choose_trump', 'playing'], true)) {
                break;
            }
            $seat = (int)$state['currentSeat'];
            $p = $state['players'][$seat];
            if (empty($p['bot']) && empty($p['away'])) {
                break;
            }
            $state = $this->botMove($state, true);
        }
        return $state;
    }

    /**
     * Legal cards for seat in current trick.
     * @param array<string,mixed> $state
     * @return array<int,string>
     */
    public function legalCards(array $state, int $seat): array
    {
        $hand = array_values((array)$state['hands'][$seat]);
        if ($state['phase'] !== 'playing') {
            return [];
        }
        if (count($state['trick']) === 0) {
            return $hand;
        }
        $leadSuit = $this->cardSuit((string)$state['trick'][0]['card']);
        $sameSuit = array_values(array_filter($hand, fn ($card) => $this->cardSuit((string)$card) === $leadSuit));
        return count($sameSuit) > 0 ? $sameSuit : $hand;
    }

    /** @param array<string,mixed> $state */
    public function stateHash(array $state, bool $includeHands = true): string
    {
        $copy = $state;
        unset($copy['security']['stateHash']);
        if (!$includeHands) {
            $copy['hands'] = array_map(static fn ($h) => is_array($h) ? count($h) : $h, (array)$copy['hands']);
        }
        return hash('sha256', json_encode($copy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    /** @param array<string,mixed> $state */
    public function isFinished(array $state): bool
    {
        return $state['phase'] === 'game_over';
    }

    /** @param array<string,mixed> $state */
    public function activeTeamScore(array $state, int $team): int
    {
        return (int)($state['scores'][$team] ?? 0);
    }

    /** @param array<int,string> $cards @return array<int,string> */
    public function sortCards(array $cards): array
    {
        usort($cards, function (string $a, string $b): int {
            $sa = self::SORT_SUIT_ORDER[$this->cardSuit($a)] ?? 99;
            $sb = self::SORT_SUIT_ORDER[$this->cardSuit($b)] ?? 99;
            if ($sa !== $sb) {
                return $sa <=> $sb;
            }
            return $this->cardPower($b) <=> $this->cardPower($a); // high to low.
        });
        return array_values($cards);
    }

    /** @return array<string,string|int> */
    public function cardInfo(string $cardId): array
    {
        $cardId = $this->normalizeCardId($cardId);
        $rank = $this->cardRank($cardId);
        $suit = $this->cardSuit($cardId);
        return [
            'id' => $cardId,
            'rank' => $rank,
            'suit' => $suit,
            'suitAr' => self::SUIT_AR[$suit],
            'suitEn' => self::SUIT_EN[$suit],
            'power' => self::RANK_POWER[$rank],
            'labelAr' => $rank . ' ' . self::SUIT_AR[$suit],
            'labelEn' => $rank . ' of ' . self::SUIT_EN[$suit],
        ];
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function startNextRound(array $state, string $reason = 'next_round'): array
    {
        $state['round'] = (int)$state['round'] + 1;
        if (!empty($state['rules']['advanceDealerEachRound'])) {
            $state['dealerSeat'] = $this->nextSeat((int)$state['dealerSeat']);
        }
        $roundSeed = ((int)$state['seed']) + ((int)$state['round'] * 9973);
        $deck = $this->buildDeck();
        $deck = $this->shuffleDeterministic($deck, $roundSeed);
        $state['hands'] = [0 => [], 1 => [], 2 => [], 3 => []];
        for ($i = 0; $i < 52; $i++) {
            $seat = $i % 4;
            $state['hands'][$seat][] = $deck[$i];
        }
        if (!empty($state['rules']['sortHands'])) {
            for ($seat = 0; $seat < 4; $seat++) {
                $state['hands'][$seat] = $this->sortCards($state['hands'][$seat]);
            }
        }
        $state['phase'] = 'bidding';
        $state['currentSeat'] = $this->nextSeat((int)$state['dealerSeat']);
        $state['roundTricks'] = [0 => 0, 1 => 0];
        $state['trump'] = null;
        $state['trick'] = [];
        $state['completedTricks'] = [];
        $state['bid'] = [
            'seat' => null,
            'team' => null,
            'amount' => 0,
            'passed' => [0 => false, 1 => false, 2 => false, 3 => false],
            'history' => [],
        ];
        foreach ($state['players'] as $seat => $p) {
            $state['players'][$seat]['missedTurns'] = 0;
        }
        $state = $this->record($state, 'round_started', [
            'round' => $state['round'],
            'dealerSeat' => $state['dealerSeat'],
            'currentSeat' => $state['currentSeat'],
            'reason' => $reason,
        ]);
        return $this->touch($state);
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function advanceBidding(array $state): array
    {
        $bid = $state['bid'];
        $passed = array_filter((array)$bid['passed'], static fn ($v) => $v === true);
        $passCount = count($passed);
        $hasBid = (int)$bid['amount'] > 0 && $bid['seat'] !== null;

        if (!$hasBid && $passCount >= 4) {
            if (!empty($state['rules']['redealOnAllPass'])) {
                $state = $this->record($state, 'all_pass_redeal', []);
                return $this->startNextRound($state, 'all_pass');
            }
            $state['phase'] = 'round_end';
            return $this->touch($state);
        }

        if ($hasBid) {
            $othersPassed = 0;
            for ($s = 0; $s < 4; $s++) {
                if ($s !== (int)$bid['seat'] && !empty($bid['passed'][$s])) {
                    $othersPassed++;
                }
            }
            if ($othersPassed >= 3 || (int)$bid['amount'] >= (int)$state['rules']['maxBid']) {
                $state['phase'] = 'choose_trump';
                $state['currentSeat'] = (int)$bid['seat'];
                $state = $this->record($state, 'bidding_won', [
                    'seat' => $bid['seat'],
                    'team' => $bid['team'],
                    'amount' => $bid['amount'],
                ]);
                return $this->touch($state);
            }
        }

        $next = $this->nextSeat((int)$state['currentSeat']);
        for ($i = 0; $i < 4; $i++) {
            if (empty($bid['passed'][$next])) {
                $state['currentSeat'] = $next;
                return $this->touch($state);
            }
            $next = $this->nextSeat($next);
        }
        return $this->touch($state);
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function scoreRound(array $state): array
    {
        $bid = $state['bid'];
        $bidTeam = (int)$bid['team'];
        $oppTeam = 1 - $bidTeam;
        $bidAmount = (int)$bid['amount'];
        $bidTricks = (int)$state['roundTricks'][$bidTeam];
        $oppTricks = (int)$state['roundTricks'][$oppTeam];
        $made = $bidTricks >= $bidAmount;
        $delta = [0 => 0, 1 => 0];

        if ($made) {
            $delta[$bidTeam] = $bidTricks;
            $delta[$oppTeam] = $oppTricks;
            if ($bidAmount === 13 && $bidTricks === 13) {
                $delta[$bidTeam] += (int)$state['rules']['bonusForThirteenBidAndMakeAll'];
            }
        } else {
            $delta[$bidTeam] = -$bidAmount;
            $delta[$oppTeam] = $oppTricks;
        }
        $state['scores'][0] = (int)$state['scores'][0] + $delta[0];
        $state['scores'][1] = (int)$state['scores'][1] + $delta[1];
        $state['phase'] = 'round_end';
        $state = $this->record($state, 'round_scored', [
            'round' => $state['round'],
            'bidTeam' => $bidTeam,
            'bidAmount' => $bidAmount,
            'bidTricks' => $bidTricks,
            'oppTricks' => $oppTricks,
            'made' => $made,
            'delta' => $delta,
            'scores' => $state['scores'],
        ]);

        $target = (int)$state['rules']['targetScore'];
        if ((int)$state['scores'][0] >= $target || (int)$state['scores'][1] >= $target) {
            $state['phase'] = 'game_over';
            $state['winnerTeam'] = (int)$state['scores'][0] === (int)$state['scores'][1]
                ? null
                : ((int)$state['scores'][0] > (int)$state['scores'][1] ? 0 : 1);
            $state = $this->record($state, 'game_over', ['winnerTeam' => $state['winnerTeam'], 'scores' => $state['scores']]);
        }
        return $this->touch($state);
    }

    /** @param array<int,array<string,mixed>> $trick */
    private function trickWinnerSeat(array $trick, string $trump): int
    {
        if (count($trick) !== 4) {
            throw new TarneebException('الخدعة غير مكتملة.', 'trick_not_complete');
        }
        $leadSuit = $this->cardSuit((string)$trick[0]['card']);
        $winner = $trick[0];
        foreach ($trick as $play) {
            $card = (string)$play['card'];
            $winCard = (string)$winner['card'];
            $cardSuit = $this->cardSuit($card);
            $winSuit = $this->cardSuit($winCard);
            $beats = false;
            if ($cardSuit === $trump && $winSuit !== $trump) {
                $beats = true;
            } elseif ($cardSuit === $winSuit && $this->cardPower($card) > $this->cardPower($winCard)) {
                $beats = true;
            } elseif ($winSuit !== $trump && $cardSuit === $leadSuit && $winSuit !== $leadSuit) {
                $beats = true;
            }
            if ($beats) {
                $winner = $play;
            }
        }
        return (int)$winner['seat'];
    }

    /** @param array<string,mixed> $state */
    private function roundIsDone(array $state): bool
    {
        for ($seat = 0; $seat < 4; $seat++) {
            if (count((array)$state['hands'][$seat]) > 0) {
                return false;
            }
        }
        return true;
    }

    /** @param array<string,mixed> $state */
    private function botSuggestedBid(array $state, int $seat): ?int
    {
        $hand = (array)$state['hands'][$seat];
        $estimate = $this->botEstimateTricks($hand);
        $current = (int)$state['bid']['amount'];
        $min = (int)$state['rules']['minBid'];
        $max = (int)$state['rules']['maxBid'];
        if ($estimate < $min || $estimate <= $current) {
            return null;
        }
        return min($max, max($min, $current + 1, $estimate));
    }

    /** @param array<int,string> $hand */
    private function botEstimateTricks(array $hand): int
    {
        $score = 0.0;
        $suitCount = ['C' => 0, 'D' => 0, 'S' => 0, 'H' => 0];
        $suitHigh = ['C' => 0.0, 'D' => 0.0, 'S' => 0.0, 'H' => 0.0];
        foreach ($hand as $card) {
            $suit = $this->cardSuit((string)$card);
            $rank = $this->cardRank((string)$card);
            $suitCount[$suit]++;
            $v = match ($rank) {
                'A' => 1.15,
                'K' => 0.80,
                'Q' => 0.45,
                'J' => 0.25,
                default => 0.05,
            };
            $score += $v;
            $suitHigh[$suit] += $v;
        }
        foreach (self::SUITS as $s) {
            if ($suitCount[$s] >= 5) {
                $score += 0.75 + (($suitCount[$s] - 5) * 0.35);
            }
            if ($suitHigh[$s] >= 2.0) {
                $score += 0.4;
            }
        }
        return (int)floor(6 + min(7, $score / 1.35));
    }

    /** @param array<string,mixed> $state */
    private function botBestTrumpSuit(array $state, int $seat): string
    {
        $hand = (array)$state['hands'][$seat];
        $score = ['C' => 0.0, 'D' => 0.0, 'S' => 0.0, 'H' => 0.0];
        foreach ($hand as $card) {
            $suit = $this->cardSuit((string)$card);
            $rank = $this->cardRank((string)$card);
            $score[$suit] += match ($rank) {
                'A' => 4.5,
                'K' => 3.5,
                'Q' => 2.2,
                'J' => 1.4,
                '10' => 0.9,
                default => 0.35,
            };
        }
        arsort($score);
        return (string)array_key_first($score);
    }

    /** @param array<string,mixed> $state */
    private function botChooseCard(array $state, int $seat): string
    {
        $legal = $this->legalCards($state, $seat);
        if (count($legal) === 0) {
            throw new TarneebException('لا توجد ورقة قانونية للبوت.', 'bot_no_legal_card');
        }
        $trick = (array)$state['trick'];
        $trump = (string)$state['trump'];
        $team = $this->teamOfSeat($state, $seat);
        $legalSortedLow = $legal;
        usort($legalSortedLow, fn ($a, $b) => $this->cardPower((string)$a) <=> $this->cardPower((string)$b));
        $legalSortedHigh = array_reverse($legalSortedLow);

        if (count($trick) === 0) {
            // Lead with a safe high card from a long suit, otherwise lowest.
            foreach ($legalSortedHigh as $card) {
                if (in_array($this->cardRank((string)$card), ['A', 'K'], true)) {
                    return (string)$card;
                }
            }
            return (string)$legalSortedLow[0];
        }

        $currentWinner = $this->partialTrickCurrentWinner($trick, $trump);
        $teammateWinning = $this->teamOfSeat($state, (int)$currentWinner['seat']) === $team;
        if ($teammateWinning) {
            return (string)$legalSortedLow[0];
        }

        foreach ($legalSortedLow as $card) {
            $candidateTrick = $trick;
            $candidateTrick[] = ['seat' => $seat, 'team' => $team, 'card' => $card, 'at' => time()];
            $winner = $this->partialTrickCurrentWinner($candidateTrick, $trump);
            if ((int)$winner['seat'] === $seat) {
                return (string)$card; // lowest winning card.
            }
        }
        return (string)$legalSortedLow[0];
    }

    /** @param array<int,array<string,mixed>> $trick @return array<string,mixed> */
    private function partialTrickCurrentWinner(array $trick, string $trump): array
    {
        $leadSuit = $this->cardSuit((string)$trick[0]['card']);
        $winner = $trick[0];
        foreach ($trick as $play) {
            $card = (string)$play['card'];
            $winCard = (string)$winner['card'];
            $cardSuit = $this->cardSuit($card);
            $winSuit = $this->cardSuit($winCard);
            if ($cardSuit === $trump && $winSuit !== $trump) {
                $winner = $play;
            } elseif ($cardSuit === $winSuit && $this->cardPower($card) > $this->cardPower($winCard)) {
                $winner = $play;
            } elseif ($winSuit !== $trump && $cardSuit === $leadSuit && $winSuit !== $leadSuit) {
                $winner = $play;
            }
        }
        return $winner;
    }

    /**
     * Normalize and validate supported house rules.
     *
     * @param array<string,mixed> $rules
     * @return array<string,mixed>
     */
    private function normalizeRules(array $rules): array
    {
        $allowedTargets = $this->targetScoreOptions();
        $target = (int)($rules['targetScore'] ?? 41);
        if (!in_array($target, $allowedTargets, true)) {
            throw new TarneebException('النتيجة النهائية يجب أن تكون 31 أو 41 أو 61 فقط.', 'invalid_target_score', [
                'allowed' => $allowedTargets,
                'received' => $rules['targetScore'] ?? null,
            ]);
        }
        $rules['targetScore'] = $target;
        $rules['allowedTargetScores'] = $allowedTargets;

        $minBid = (int)($rules['minBid'] ?? 7);
        $maxBid = (int)($rules['maxBid'] ?? 13);
        if ($minBid < 7 || $minBid > 13 || $maxBid < 7 || $maxBid > 13 || $minBid > $maxBid) {
            throw new TarneebException('مدى الطلب يجب أن يكون صحيحًا بين 7 و 13.', 'invalid_bid_range', [
                'minBid' => $minBid,
                'maxBid' => $maxBid,
            ]);
        }
        $rules['minBid'] = $minBid;
        $rules['maxBid'] = $maxBid;

        $rules['playersCount'] = 4;
        $rules['cardsPerPlayer'] = 13;
        $rules['teams'] = [0 => 0, 1 => 1, 2 => 0, 3 => 1];

        return $rules;
    }

    /** @param array<int,array<string,mixed>> $players */
    private function assertPlayers(array $players): void
    {
        if (count($players) !== 4) {
            throw new TarneebException('طرنيب تحتاج 4 لاعبين فقط.', 'tarneeb_requires_4_players');
        }
        $ids = [];
        foreach ($players as $p) {
            if (empty($p['id'])) {
                throw new TarneebException('كل لاعب يحتاج id.', 'player_id_required');
            }
            $id = (string)$p['id'];
            if (isset($ids[$id])) {
                throw new TarneebException('لا يمكن تكرار نفس اللاعب في اللعبة.', 'duplicate_player');
            }
            $ids[$id] = true;
        }
    }

    /** @param array<string,mixed> $state */
    private function assertPhase(array $state, string $phase): void
    {
        if (($state['phase'] ?? null) !== $phase) {
            throw new TarneebException('هذه الحركة غير متاحة الآن.', 'wrong_phase', ['expected' => $phase, 'actual' => $state['phase'] ?? null]);
        }
    }

    /** @param array<string,mixed> $state */
    private function assertCurrentSeat(array $state, int $seat): void
    {
        if ((int)$state['currentSeat'] !== $seat) {
            throw new TarneebException('ليس دورك الآن.', 'not_your_turn', ['currentSeat' => $state['currentSeat'], 'yourSeat' => $seat]);
        }
    }

    /** @param array<string,mixed> $state */
    private function assertPlayerCanAct(array $state, int $seat): void
    {
        if (!empty($state['players'][$seat]['bannedFromGame'])) {
            throw new TarneebException('هذا اللاعب محظور من هذه اللعبة.', 'player_banned_from_game');
        }
    }

    /** @param array<string,mixed> $state */
    private function seatOfPlayer(array $state, string $playerId): int
    {
        foreach ((array)$state['players'] as $seat => $p) {
            if ((string)$p['id'] === $playerId) {
                return (int)$seat;
            }
        }
        throw new TarneebException('اللاعب غير موجود في هذه اللعبة.', 'player_not_in_game');
    }

    /** @param array<string,mixed> $state */
    private function teamOfSeat(array $state, int $seat): int
    {
        return (int)$state['players'][$seat]['team'];
    }

    private function nextSeat(int $seat): int
    {
        return ($seat + 1) % 4;
    }

    /** @return array<int,string> */
    private function buildDeck(): array
    {
        $deck = [];
        foreach (self::SUITS as $suit) {
            foreach (self::RANKS as $rank) {
                $deck[] = $rank . '_' . $suit;
            }
        }
        return $deck;
    }

    /** @param array<int,string> $deck @return array<int,string> */
    private function shuffleDeterministic(array $deck, int $seed): array
    {
        $rand = $seed % 2147483647;
        $nextRand = static function () use (&$rand): int {
            $rand = (int)(($rand * 48271) % 2147483647);
            return $rand;
        };
        for ($i = count($deck) - 1; $i > 0; $i--) {
            $j = $nextRand() % ($i + 1);
            $tmp = $deck[$i];
            $deck[$i] = $deck[$j];
            $deck[$j] = $tmp;
        }
        return array_values($deck);
    }

    private function normalizeCardId(string $cardId): string
    {
        $cardId = strtoupper(trim($cardId));
        $cardId = str_replace(['-', ' '], '_', $cardId);
        if (!preg_match('/^(A|K|Q|J|10|9|8|7|6|5|4|3|2)_(C|D|S|H)$/', $cardId)) {
            throw new TarneebException('رمز الورقة غير صحيح.', 'invalid_card_id', ['card' => $cardId]);
        }
        return $cardId;
    }

    private function cardRank(string $cardId): string
    {
        $cardId = $this->normalizeCardId($cardId);
        return explode('_', $cardId)[0];
    }

    private function cardSuit(string $cardId): string
    {
        $cardId = $this->normalizeCardId($cardId);
        return explode('_', $cardId)[1];
    }

    private function cardPower(string $cardId): int
    {
        return self::RANK_POWER[$this->cardRank($cardId)];
    }

    /** @param array<string,mixed> $state */
    private function handHasCard(array $state, int $seat, string $cardId): bool
    {
        return in_array($cardId, (array)$state['hands'][$seat], true);
    }

    /** @param array<string,mixed> $state @param array<string,mixed> $payload @return array<string,mixed> */
    private function record(array $state, string $type, array $payload): array
    {
        $state['security']['lastActionNo'] = (int)($state['security']['lastActionNo'] ?? 0) + 1;
        $event = [
            'no' => $state['security']['lastActionNo'],
            'type' => $type,
            'payload' => $payload,
            'at' => time(),
        ];
        $state['events'][] = $event;
        $state['replay'][] = $event;
        if (count($state['events']) > 80) {
            $state['events'] = array_slice($state['events'], -80);
        }
        return $state;
    }

    /** @param array<string,mixed> $state @return array<string,mixed> */
    private function touch(array $state): array
    {
        $state['updatedAt'] = time();
        $state['security']['stateHash'] = $this->stateHash($state, true);
        return $state;
    }

    private function newId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }
}
