<?php
/**
 * Warqna Global Card Engine Core
 * ------------------------------------------------------------
 * مستقل عن Laravel. كل الحركات تمر عبر applyAction/availableActions.
 * Server-authoritative: لا يثق بالواجهة، ويتحقق من الدور والورق والقانون.
 */
class GameEngineException extends Exception {}

class GlobalCardEngineCore
{
    protected array $config = [];
    protected string $engineName = 'global';

    public function __construct(array $overrides = [])
    {
        $this->config = array_replace_recursive($this->defaultConfig(), $overrides);
    }

    protected function defaultConfig(): array { return []; }

    public function newGame(array $players, array $options = []): array
    {
        $cfg = array_replace_recursive($this->config, $options);
        $this->validatePlayers($players, $cfg);
        $seed = $options['seed'] ?? random_int(100000, 999999999);
        mt_srand((int)$seed);
        $deck = $this->makeDeck($cfg['deck'] ?? '52', count($players));
        $this->shuffleDeck($deck);
        $hands = [];
        $tableau = [];
        $foundation = [];
        $discard = [];
        $mode = $cfg['mode'];

        foreach ($players as $i => $p) {
            $pid = (string)($p['id'] ?? ('p'.($i+1)));
            $hands[$pid] = [];
            $tableau[$pid] = [];
            $foundation[$pid] = [];
        }

        if (in_array($mode, ['trick','trick400','trix','trix-complex'], true)) {
            $cardsEach = intdiv(count($deck), count($players));
            for ($r=0; $r<$cardsEach; $r++) {
                foreach ($players as $p) $hands[(string)$p['id']][] = array_shift($deck);
            }
            foreach ($hands as $pid => $h) $hands[$pid] = $this->sortCards($h);
            $phase = in_array($mode, ['trick','trick400'], true) ? 'bidding' : 'contract';
        } elseif ($mode === 'baloot') {
            for ($r=0; $r<8; $r++) foreach ($players as $p) $hands[(string)$p['id']][] = array_shift($deck);
            foreach ($hands as $pid => $h) $hands[$pid] = $this->sortCards($h);
            $phase = 'bidding';
        } elseif ($mode === 'solitaire') {
            foreach ($players as $p) {
                $pid = (string)$p['id'];
                $tableau[$pid] = array_splice($deck, 0, 7);
                $hands[$pid] = array_splice($deck, 0, 24); // stock
                $foundation[$pid] = ['C'=>[], 'D'=>[], 'S'=>[], 'H'=>[]];
            }
            $phase = 'playing';
        } else { // rummy / hand / banakil
            $cardsEach = (int)($cfg['cardsEach'] ?? 14);
            for ($r = 0; $r < $cardsEach; $r++) {
                foreach ($players as $p) {
                    $hands[(string)$p['id']][] = array_shift($deck);
                }
            }
            // Some regional variants start the first player with an extra card so
            // the opening turn begins with a discard instead of an artificial draw.
            $firstExtra = max(0, (int)($cfg['firstExtra'] ?? 0));
            for ($i = 0; $i < $firstExtra; $i++) {
                $hands[(string)$players[0]['id']][] = array_shift($deck);
            }
            foreach ($hands as $pid => $h) {
                $hands[$pid] = $this->sortCards($h);
            }
            $discard[] = array_shift($deck);
            $phase = $firstExtra > 0 ? 'discard' : 'draw';
        }

        $state = [
            'engine' => $this->engineName,
            'version' => 'final-v1',
            'seed' => $seed,
            'config' => $cfg,
            'players' => array_values(array_map(fn($p,$i)=>[
                'id'=>(string)($p['id'] ?? ('p'.($i+1))),
                'name'=>(string)($p['name'] ?? ('Player '.($i+1))),
                'seat'=>$i,
                'team'=>($cfg['partnership'] ?? false) ? ($i % 2) : $i,
                'bot'=>(bool)($p['bot'] ?? false),
                'away'=>false,
                'connected'=>true,
                'missedTurns'=>0,
            ], $players, array_keys($players))),
            'phase'=>$phase,
            'currentIndex'=>0,
            'dealerIndex'=>0,
            'hands'=>$hands,
            'deck'=>$deck,
            'discard'=>$discard,
            'melds'=>[],
            // Opening status is player-scoped in individual games and may be
            // evaluated as a team by clients when partnership rules request it.
            'opened'=>array_fill_keys(array_keys($hands), false),
            'turnMeldValue'=>array_fill_keys(array_keys($hands), 0),
            'tableau'=>$tableau,
            'foundation'=>$foundation,
            'bids'=>[],
            'highestBid'=>null,
            'bidWinner'=>null,
            'trump'=>null,
            'contract'=>null,
            'trick'=>[],
            'tricksWon'=>[],
            'scores'=>$this->initialScores($players, $cfg),
            'round'=>1,
            'gameOver'=>false,
            'winner'=>null,
            'events'=>[],
            'antiCheat'=>[
                'lastHash'=>null,
                'moveCounter'=>0,
                'illegalMoves'=>[],
            ],
        ];
        $state = $this->record($state, 'game.created', ['players'=>count($players), 'mode'=>$mode]);
        return $this->finalizeState($state);
    }

    protected function validatePlayers(array $players, array $cfg): void
    {
        $allowed = $cfg['players'] ?? [4];
        if (!in_array(count($players), $allowed, true)) throw new GameEngineException('عدد اللاعبين غير مسموح لهذه اللعبة.');
        $ids = [];
        foreach ($players as $p) {
            if (empty($p['id'])) throw new GameEngineException('كل لاعب يحتاج id.');
            if (isset($ids[$p['id']])) throw new GameEngineException('تكرار id لاعب.');
            $ids[$p['id']] = true;
        }
    }

    protected function initialScores(array $players, array $cfg): array
    {
        $scores = [];
        if ($cfg['partnership'] ?? false) {
            $scores = [0=>0, 1=>0];
        } else {
            foreach ($players as $i => $p) $scores[(string)$p['id']] = 0;
        }
        return $scores;
    }

    protected function makeDeck(string $type, int $players): array
    {
        $ranks = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];
        $suits = ['C','D','S','H'];
        if ($type === 'baloot32') $ranks = ['7','8','9','J','Q','K','10','A'];
        $deck = [];
        foreach ($suits as $s) foreach ($ranks as $r) $deck[] = $r.'_'.$s;
        if (in_array($type, ['double-joker','multi52'], true)) {
            $deck = array_merge($deck, $deck);
            $deck[] = 'JOKER_R'; $deck[] = 'JOKER_B';
        }
        if ($type === 'multi52') {
            while (count($deck) < ($players * 40)) $deck = array_merge($deck, $deck);
        }
        return array_values($deck);
    }

    protected function shuffleDeck(array &$deck): void
    {
        for ($i=count($deck)-1; $i>0; $i--) { $j=mt_rand(0,$i); [$deck[$i],$deck[$j]]=[$deck[$j],$deck[$i]]; }
    }

    public function availableActions(array $state, string $playerId): array
    {
        $this->assertPlayer($state, $playerId);
        if ($state['gameOver']) return [];
        $current = $this->currentPlayerId($state);
        $isTurn = $current === $playerId;
        $mode = $state['config']['mode'];
        $actions = [];
        if (!$isTurn) return [['type'=>'wait','reason'=>'ليس دورك الآن']];

        if ($state['phase'] === 'bidding') {
            $actions[] = ['type'=>'pass'];
            $min = (int)($state['config']['minBid'] ?? 7);
            if ($state['highestBid']) $min = max($min, (int)$state['highestBid']['amount'] + 1);
            $max = (int)($state['config']['maxBid'] ?? 13);
            for ($b=$min; $b<=$max; $b++) $actions[] = ['type'=>'bid','amount'=>$b];
            return $actions;
        }
        if ($state['phase'] === 'choose_trump') {
            foreach (['C','D','S','H'] as $s) $actions[] = ['type'=>'choose_trump','suit'=>$s];
            return $actions;
        }
        if ($state['phase'] === 'contract') {
            $contracts = $mode === 'trix-complex' ? ['complex','trix'] : ['tricks','girls','diamonds','king_hearts','trix'];
            foreach ($contracts as $c) $actions[] = ['type'=>'choose_contract','contract'=>$c];
            return $actions;
        }
        if (in_array($mode, ['trick','trick400','trix','trix-complex','baloot'], true)) {
            foreach ($this->legalCards($state, $playerId) as $card) $actions[] = ['type'=>'play_card','card'=>$card];
            return $actions;
        }
        if ($mode === 'solitaire') {
            if (!empty($state['hands'][$playerId] ?? [])) $actions[] = ['type'=>'draw_stock'];
            foreach (($state['tableau'][$playerId] ?? []) as $card) {
                $s = $this->suit($card);
                $need = count($state['foundation'][$playerId][$s] ?? []) + 1;
                if ($this->rankValue($card) === $need) $actions[] = ['type'=>'move_to_foundation','card'=>$card];
            }
            return $actions;
        }
        if ($state['phase'] === 'draw') {
            $actions[] = ['type'=>'draw_deck'];
            if (!empty($state['discard'])) $actions[] = ['type'=>'draw_discard'];
            return $actions;
        }
        // Rummy / Hand / Banakil: draw first, then the player may open with
        // one atomic batch (several legal groups whose total reaches the opening
        // threshold), add later melds, lay off on an existing meld, then discard.
        $actions[] = ['type'=>'organize','strategy'=>'smart'];
        $suggestions = $this->suggestMelds(
            $state['hands'][$playerId] ?? [],
            (int)($state['config']['opening'] ?? 51),
            $state['config'] ?? []
        );
        foreach ($suggestions as $meld) {
            $actions[] = ['type'=>'meld','cards'=>$meld['cards'],'value'=>$meld['value']];
        }
        if (!($state['opened'][$playerId] ?? false)) {
            $batch = $this->suggestOpeningBatch($suggestions, (int)($state['config']['opening'] ?? 51));
            if ($batch !== []) {
                $actions[] = ['type'=>'meld_batch','groups'=>$batch];
            }
        } else {
            $layOffCount = 0;
            foreach (($state['melds'] ?? []) as $ownerId => $ownerMelds) {
                foreach (($ownerMelds ?? []) as $meldIndex => $existingMeld) {
                    foreach (($state['hands'][$playerId] ?? []) as $card) {
                        $combined = array_merge($existingMeld['cards'] ?? [], [$card]);
                        if ($this->isValidMeld($combined, $state['config'] ?? [])) {
                            $actions[] = [
                                'type'=>'lay_off',
                                'owner'=>(string)$ownerId,
                                'meld_index'=>(int)$meldIndex,
                                'cards'=>[$card],
                            ];
                            $layOffCount++;
                            if ($layOffCount >= 24) break 3;
                        }
                    }
                }
            }
        }
        foreach (($state['hands'][$playerId] ?? []) as $card) {
            $actions[] = ['type'=>'discard','card'=>$card];
        }
        return $actions;
    }

    public function applyAction(array $state, string $playerId, array $action): array
    {
        $this->assertPlayer($state, $playerId);
        if ($state['gameOver']) throw new GameEngineException('اللعبة منتهية.');
        if ($this->currentPlayerId($state) !== $playerId && !in_array(($action['type'] ?? ''), ['set_away','return_from_away'], true)) throw new GameEngineException('ليست دورك.');
        $type = (string)($action['type'] ?? '');
        $mode = $state['config']['mode'];
        return match($type) {
            'pass' => $this->pass($state, $playerId),
            'bid' => $this->bid($state, $playerId, (int)$action['amount']),
            'choose_trump' => $this->chooseTrump($state, $playerId, (string)$action['suit']),
            'choose_contract' => $this->chooseContract($state, $playerId, (string)$action['contract']),
            'play_card' => $this->playCard($state, $playerId, (string)$action['card']),
            'draw_deck' => $this->drawDeck($state, $playerId),
            'draw_discard' => $this->drawDiscard($state, $playerId),
            'discard' => $this->discardCard($state, $playerId, (string)$action['card']),
            'meld' => $this->meld($state, $playerId, $action['cards'] ?? []),
            'meld_batch' => $this->meldBatch($state, $playerId, $action['groups'] ?? []),
            'lay_off' => $this->layOff(
                $state,
                $playerId,
                (string)($action['owner'] ?? $playerId),
                (int)($action['meld_index'] ?? -1),
                $action['cards'] ?? []
            ),
            'organize' => $this->organize($state, $playerId, (string)($action['strategy'] ?? 'smart')),
            'draw_stock' => $this->solitaireDraw($state, $playerId),
            'move_to_foundation' => $this->solitaireFoundation($state, $playerId, (string)$action['card']),
            'set_away' => $this->setAway($state, $playerId, true),
            'return_from_away' => $this->setAway($state, $playerId, false),
            default => throw new GameEngineException('حركة غير معروفة: '.$type),
        };
    }

    protected function pass(array $state, string $playerId): array
    {
        if ($state['phase'] !== 'bidding') throw new GameEngineException('لا يوجد طلب الآن.');
        $state['bids'][] = ['player'=>$playerId, 'amount'=>null];
        $state = $this->record($state, 'bid.pass', compact('playerId'));
        if (count($state['bids']) >= count($state['players'])) {
            if (!$state['highestBid']) {
                $state = $this->record($state, 'round.redeal', ['reason'=>'all_passed']);
                return $this->newGame($state['players'], $state['config']);
            }
            $state['phase'] = ($state['config']['trump'] ?? false) ? 'choose_trump' : 'playing';
            $state['currentIndex'] = $this->playerIndex($state, $state['bidWinner']);
        } else $state = $this->advance($state);
        return $this->finalizeState($state);
    }

    protected function bid(array $state, string $playerId, int $amount): array
    {
        if ($state['phase'] !== 'bidding') throw new GameEngineException('مرحلة الطلب غير فعالة.');
        $min = (int)($state['config']['minBid'] ?? 7); $max=(int)($state['config']['maxBid'] ?? 13);
        if ($state['highestBid']) $min = max($min, (int)$state['highestBid']['amount'] + 1);
        if ($amount < $min || $amount > $max) throw new GameEngineException('طلب غير مسموح.');
        $state['bids'][] = ['player'=>$playerId, 'amount'=>$amount];
        $state['highestBid'] = ['player'=>$playerId, 'amount'=>$amount];
        $state['bidWinner'] = $playerId;
        $state = $this->record($state, 'bid.made', compact('playerId','amount'));
        if ($amount >= $max) { $state['phase'] = 'choose_trump'; $state['currentIndex']=$this->playerIndex($state,$playerId); }
        else $state = $this->advance($state);
        return $this->finalizeState($state);
    }

    protected function chooseTrump(array $state, string $playerId, string $suit): array
    {
        if ($state['phase'] !== 'choose_trump') throw new GameEngineException('ليست مرحلة اختيار الحكم/الطرنيب.');
        if ($state['bidWinner'] !== $playerId) throw new GameEngineException('اختيار الطرنيب لصاحب أعلى طلب فقط.');
        if (!in_array($suit, ['C','D','S','H'], true)) throw new GameEngineException('نوع غير صحيح.');
        $state['trump'] = $suit; $state['phase'] = 'playing';
        $state = $this->record($state, 'trump.chosen', compact('playerId','suit'));
        return $this->finalizeState($state);
    }

    protected function chooseContract(array $state, string $playerId, string $contract): array
    {
        if ($state['phase'] !== 'contract') throw new GameEngineException('ليست مرحلة اختيار العقد.');
        $allowed = ['tricks','girls','diamonds','king_hearts','trix','complex'];
        if (!in_array($contract, $allowed, true)) throw new GameEngineException('عقد غير مسموح.');
        $state['contract'] = $contract; $state['phase']='playing';
        $state = $this->record($state, 'contract.chosen', compact('playerId','contract'));
        return $this->finalizeState($state);
    }

    protected function legalCards(array $state, string $playerId): array
    {
        $hand = $state['hands'][$playerId] ?? [];
        if (empty($state['trick'])) return $hand;
        $leadSuit = $this->suit($state['trick'][0]['card']);
        $same = array_values(array_filter($hand, fn($c)=>$this->suit($c)===$leadSuit));
        return $same ?: $hand;
    }

    protected function playCard(array $state, string $playerId, string $card): array
    {
        if (!in_array($state['config']['mode'], ['trick','trick400','trix','trix-complex','baloot'], true)) throw new GameEngineException('هذه الحركة ليست لهذه اللعبة.');
        if (!in_array($card, $state['hands'][$playerId] ?? [], true)) throw new GameEngineException('الورقة ليست في يد اللاعب.');
        if (!in_array($card, $this->legalCards($state,$playerId), true)) throw new GameEngineException('يجب اتباع نوع الورقة إذا كان موجودًا.');
        $this->removeOneCard($state['hands'][$playerId], $card);
        $state['trick'][] = ['player'=>$playerId, 'card'=>$card];
        $state = $this->record($state, 'card.played', compact('playerId','card'));
        if (count($state['trick']) >= count($state['players'])) {
            $winner = $this->trickWinner($state);
            $team = $this->teamOf($state, $winner);
            $state['tricksWon'][$team] = ($state['tricksWon'][$team] ?? 0) + 1;
            $state = $this->record($state, 'trick.won', ['winner'=>$winner,'team'=>$team,'cards'=>$state['trick']]);
            $state['trick'] = [];
            $state['currentIndex'] = $this->playerIndex($state, $winner);
            if ($this->allHandsEmpty($state)) $state = $this->scoreTrickRound($state);
        } else $state = $this->advance($state);
        return $this->finalizeState($state);
    }

    protected function trickWinner(array $state): string
    {
        $leadSuit = $this->suit($state['trick'][0]['card']);
        $trump = $state['trump'] ?? null;
        $winner = $state['trick'][0];
        foreach ($state['trick'] as $play) {
            if ($this->cardBeats($play['card'], $winner['card'], $leadSuit, $trump, $state['config']['mode'])) $winner = $play;
        }
        return $winner['player'];
    }

    protected function cardBeats(string $a, string $b, string $lead, ?string $trump, string $mode): bool
    {
        $as=$this->suit($a); $bs=$this->suit($b);
        if ($trump && $as===$trump && $bs!==$trump) return true;
        if ($trump && $as!==$trump && $bs===$trump) return false;
        if ($as===$bs) return $this->rankValue($a, $mode, $trump) > $this->rankValue($b, $mode, $trump);
        if ($as===$lead && $bs!==$lead) return true;
        return false;
    }

    protected function scoreTrickRound(array $state): array
    {
        $mode = $state['config']['mode'];
        if (in_array($mode, ['trix','trix-complex'], true)) {
            $penalties = $this->trixPenaltiesFromEvents($state);
            foreach ($penalties as $pid=>$pts) $state['scores'][$pid] = ($state['scores'][$pid] ?? 0) + $pts;
        } else {
            $bidTeam = $this->teamOf($state, $state['bidWinner'] ?? $state['players'][0]['id']);
            $bid = (int)($state['highestBid']['amount'] ?? 0);
            $won = (int)($state['tricksWon'][$bidTeam] ?? 0);
            $unit = $mode === 'trick400' ? 20 : 1;
            if ($won >= $bid) $state['scores'][$bidTeam] = ($state['scores'][$bidTeam] ?? 0) + ($won * $unit);
            else $state['scores'][$bidTeam] = ($state['scores'][$bidTeam] ?? 0) - ($bid * $unit);
            foreach ($state['scores'] as $team=>$score) {
                if ((string)$team !== (string)$bidTeam) $state['scores'][$team] = ($state['scores'][$team] ?? 0) + (($state['tricksWon'][$team] ?? 0) * $unit);
            }
        }
        $state = $this->record($state, 'round.scored', ['scores'=>$state['scores'], 'tricks'=>$state['tricksWon']]);
        foreach ($state['scores'] as $key=>$score) {
            if ($score >= (int)($state['config']['targetScore'] ?? 41)) { $state['gameOver']=true; $state['winner']=$key; }
        }
        if (!$state['gameOver']) $state = $this->newRoundFromState($state);
        return $state;
    }

    protected function trixPenaltiesFromEvents(array $state): array
    {
        $pen = [];
        $contract = $state['contract'] ?? 'tricks';
        foreach ($state['players'] as $p) $pen[(string)$p['id']] = 0;
        foreach ($state['events'] as $e) if (($e['type'] ?? '') === 'trick.won') {
            $winner = $e['data']['winner'];
            $cards = array_column($e['data']['cards'] ?? [], 'card');
            if ($contract === 'tricks') $pen[$winner] -= 15;
            if ($contract === 'girls') foreach ($cards as $c) if (str_starts_with($c,'Q_')) $pen[$winner] -= 25;
            if ($contract === 'diamonds') foreach ($cards as $c) if (str_ends_with($c,'_D')) $pen[$winner] -= 10;
            if ($contract === 'king_hearts') foreach ($cards as $c) if ($c==='K_H') $pen[$winner] -= 75;
            if ($contract === 'complex') { foreach ($cards as $c) { if (str_starts_with($c,'Q_')) $pen[$winner]-=25; if(str_ends_with($c,'_D'))$pen[$winner]-=10; if($c==='K_H')$pen[$winner]-=75; } $pen[$winner]-=15; }
            if ($contract === 'trix') $pen[$winner] += 50;
        }
        return $pen;
    }

    protected function newRoundFromState(array $old): array
    {
        $players = $old['players'];
        $new = $this->newGame($players, $old['config']);
        $new['scores'] = $old['scores'];
        $new['round'] = ($old['round'] ?? 1) + 1;
        $new['events'] = $old['events'];
        return $this->record($new, 'round.started', ['round'=>$new['round']]);
    }

    protected function drawDeck(array $state, string $playerId): array
    {
        if ($state['phase'] !== 'draw') {
            throw new GameEngineException('يجب أن تكون في مرحلة السحب.');
        }
        if (empty($state['deck'])) {
            if (count($state['discard']) <= 1) {
                throw new GameEngineException('لا يوجد ورق كافٍ لإعادة تكوين رزمة السحب.');
            }
            $top = array_pop($state['discard']);
            $state['deck'] = array_values($state['discard']);
            $state['discard'] = [$top];
            $this->shuffleDeck($state['deck']);
            $state = $this->record($state, 'rummy.deck_recycled', ['cards'=>count($state['deck'])]);
        }
        $card = array_shift($state['deck']);
        if (!$card) {
            throw new GameEngineException('لا يوجد ورق للسحب.');
        }
        $state['hands'][$playerId][] = $card;
        $state['hands'][$playerId] = $this->sortCards($state['hands'][$playerId]);
        $state['phase'] = 'discard';
        $state['turnMeldValue'][$playerId] = 0;
        $state = $this->record($state, 'rummy.draw_deck', compact('playerId'));
        return $this->finalizeState($state);
    }

    protected function drawDiscard(array $state, string $playerId): array
    {
        if ($state['phase'] !== 'draw') throw new GameEngineException('يجب أن تكون في مرحلة السحب.');
        if (empty($state['discard'])) throw new GameEngineException('الرمي فارغ.');
        $card = array_pop($state['discard']);
        $state['hands'][$playerId][] = $card;
        $state['hands'][$playerId] = $this->sortCards($state['hands'][$playerId]);
        $state['phase'] = 'discard';
        $state['turnMeldValue'][$playerId] = 0;
        $state = $this->record($state, 'rummy.draw_discard', ['playerId'=>$playerId,'card'=>$card]);
        return $this->finalizeState($state);
    }

    protected function discardCard(array $state, string $playerId, string $card): array
    {
        if ($state['phase'] !== 'discard') {
            throw new GameEngineException('يجب السحب قبل الرمي.');
        }
        if (!$this->removeOneCard($state['hands'][$playerId], $card)) {
            throw new GameEngineException('الورقة ليست في اليد.');
        }
        $state['discard'][] = $card;
        $state = $this->record($state, 'rummy.discard', compact('playerId','card'));
        if (empty($state['hands'][$playerId])) {
            $state = $this->scoreRummyRound($state, $playerId, false);
        } else {
            $state['phase'] = 'draw';
            $state['turnMeldValue'][$playerId] = 0;
            $state = $this->advance($state);
        }
        return $this->finalizeState($state);
    }

    protected function meld(array $state, string $playerId, array $cards): array
    {
        return $this->meldBatch($state, $playerId, [$cards]);
    }

    /**
     * Atomically validates and places one or more groups. This is essential for
     * a real 51-point opening: several groups may be combined, and none are
     * removed from the hand unless the complete opening is legal.
     */
    protected function meldBatch(array $state, string $playerId, array $groups): array
    {
        if ($state['phase'] !== 'discard') {
            throw new GameEngineException('يمكن التنزيل بعد السحب فقط.');
        }
        if ($groups === [] || count($groups) > 8) {
            throw new GameEngineException('اختر مجموعة واحدة على الأقل للتنزيل.');
        }

        $handCopy = array_values($state['hands'][$playerId] ?? []);
        $normalized = [];
        $total = 0;
        foreach ($groups as $group) {
            $cards = array_values(array_map('strval', is_array($group) ? $group : []));
            if (count($cards) < 3 || count($cards) > 13) {
                throw new GameEngineException('كل مجموعة أو سلسلة يجب أن تكون من 3 إلى 13 ورقة.');
            }
            if (!$this->cardsContained($handCopy, $cards)) {
                throw new GameEngineException('تتضمن المجموعة ورقة غير موجودة أو مستخدمة أكثر من مرة.');
            }
            if (!$this->isValidMeld($cards, $state['config'] ?? [])) {
                throw new GameEngineException('المجموعة أو السلسلة غير صحيحة.');
            }
            foreach ($cards as $card) {
                $this->removeOneCard($handCopy, $card);
            }
            $value = $this->meldValue($cards, $state['config'] ?? []);
            $normalized[] = ['cards'=>$this->sortMeldCards($cards), 'value'=>$value];
            $total += $value;
        }

        $opening = max(0, (int)($state['config']['opening'] ?? 51));
        if (!($state['opened'][$playerId] ?? false) && $total < $opening) {
            throw new GameEngineException('مجموع الافتتاح أقل من '.$opening.' نقطة. يمكنك جمع أكثر من مجموعة في نزول واحد.');
        }

        $state['hands'][$playerId] = $this->sortCards($handCopy);
        foreach ($normalized as $meld) {
            $state['melds'][$playerId][] = $meld;
        }
        $state['opened'][$playerId] = true;
        $state['turnMeldValue'][$playerId] = (int)($state['turnMeldValue'][$playerId] ?? 0) + $total;
        $state = $this->record($state, 'rummy.meld_batch', [
            'playerId'=>$playerId,
            'groups'=>$normalized,
            'value'=>$total,
            'opening'=>$opening,
        ]);
        if (empty($state['hands'][$playerId])) {
            $state = $this->scoreRummyRound($state, $playerId, true);
        }
        return $this->finalizeState($state);
    }

    protected function layOff(array $state, string $playerId, string $ownerId, int $meldIndex, array $cards): array
    {
        if ($state['phase'] !== 'discard') {
            throw new GameEngineException('يمكن التركيب بعد السحب فقط.');
        }
        if (!($state['opened'][$playerId] ?? false)) {
            throw new GameEngineException('يجب إكمال نزولك الأول قبل التركيب على المجموعات.');
        }
        if (!isset($state['melds'][$ownerId][$meldIndex])) {
            throw new GameEngineException('المجموعة المطلوبة غير موجودة.');
        }
        $cards = array_values(array_map('strval', $cards));
        if ($cards === [] || !$this->cardsContained($state['hands'][$playerId] ?? [], $cards)) {
            throw new GameEngineException('ورق التركيب غير موجود في اليد.');
        }
        $combined = array_merge($state['melds'][$ownerId][$meldIndex]['cards'] ?? [], $cards);
        if (!$this->isValidMeld($combined, $state['config'] ?? [])) {
            throw new GameEngineException('لا يمكن تركيب هذه الأوراق على المجموعة المحددة.');
        }
        foreach ($cards as $card) {
            $this->removeOneCard($state['hands'][$playerId], $card);
        }
        $state['hands'][$playerId] = $this->sortCards($state['hands'][$playerId]);
        $state['melds'][$ownerId][$meldIndex] = [
            'cards'=>$this->sortMeldCards($combined),
            'value'=>$this->meldValue($combined, $state['config'] ?? []),
        ];
        $state = $this->record($state, 'rummy.lay_off', compact('playerId','ownerId','meldIndex','cards'));
        if (empty($state['hands'][$playerId])) {
            $state = $this->scoreRummyRound($state, $playerId, true);
        }
        return $this->finalizeState($state);
    }

    protected function isValidMeld(array $cards, array $cfg = []): bool
    {
        if (count($cards) < 3 || count($cards) > 13) {
            return false;
        }
        $wild = array_values(array_filter($cards, fn($card) => $this->isWildCard($card, $cfg)));
        $natural = array_values(array_filter($cards, fn($card) => !$this->isWildCard($card, $cfg)));
        if (count($natural) < 2) {
            return (bool)($cfg['allowTwoNaturalLessMeld'] ?? false);
        }

        $ranks = array_map(fn($card) => $this->rank($card), $natural);
        $suits = array_map(fn($card) => $this->suit($card), $natural);
        $wildCount = count($wild);

        // Set: one rank, no duplicate exact card in a standard two-deck set.
        if (count(array_unique($ranks)) === 1) {
            return count(array_unique($natural)) === count($natural)
                && count($cards) <= (int)($cfg['maxSetSize'] ?? 4);
        }

        // Run: one suit, unique ranks, Ace may be low or high but never wraps.
        if (count(array_unique($suits)) !== 1 || count(array_unique($ranks)) !== count($ranks)) {
            return false;
        }
        $values = array_map(fn($card) => $this->rummyRankValue($card), $natural);
        sort($values);
        $gapHigh = $this->sequenceGap($values);
        $aceLowValues = array_map(fn($value) => $value === 14 ? 1 : $value, $values);
        sort($aceLowValues);
        $gapLow = $this->sequenceGap($aceLowValues);
        return min($gapHigh, $gapLow) <= $wildCount;
    }

    protected function sequenceGap(array $values): int
    {
        $gaps = 0;
        for ($i = 1; $i < count($values); $i++) {
            if ($values[$i] <= $values[$i - 1]) {
                return PHP_INT_MAX;
            }
            $gaps += max(0, $values[$i] - $values[$i - 1] - 1);
        }
        return $gaps;
    }

    protected function isWildCard(string $card, array $cfg = []): bool
    {
        return str_starts_with($card, 'JOKER')
            || (($cfg['wildTwos'] ?? false) && $this->rank($card) === '2');
    }

    protected function meldValue(array $cards, array $cfg = []): int
    {
        return array_sum(array_map(function (string $card) use ($cfg): int {
            if ($this->isWildCard($card, $cfg)) {
                return (int)($cfg['wildValue'] ?? 20);
            }
            $rank = $this->rank($card);
            if ($rank === 'A') {
                return 11;
            }
            if (in_array($rank, ['K','Q','J','10'], true)) {
                return 10;
            }
            return max(0, (int)$rank);
        }, $cards));
    }

    protected function suggestMelds(array $hand, int $opening, array $cfg = []): array
    {
        $out = [];
        $n = count($hand);
        // Candidate sets by rank.
        $byRank = [];
        foreach ($hand as $card) {
            if (!$this->isWildCard($card, $cfg)) {
                $byRank[$this->rank($card)][] = $card;
            }
        }
        foreach ($byRank as $cards) {
            if (count(array_unique($cards)) >= 3) {
                $candidate = array_slice(array_values(array_unique($cards)), 0, 4);
                if ($this->isValidMeld($candidate, $cfg)) {
                    $out[] = ['cards'=>$candidate,'value'=>$this->meldValue($candidate, $cfg)];
                }
            }
        }
        // Candidate natural runs; clients may submit longer/wildcard runs and the
        // server validates them authoritatively even if not suggested here.
        $bySuit = [];
        foreach ($hand as $card) {
            if (!$this->isWildCard($card, $cfg)) {
                $bySuit[$this->suit($card)][] = $card;
            }
        }
        foreach ($bySuit as $cards) {
            usort($cards, fn($a, $b) => $this->rummyRankValue($a) <=> $this->rummyRankValue($b));
            for ($start = 0; $start < count($cards); $start++) {
                $run = [$cards[$start]];
                for ($i = $start + 1; $i < count($cards); $i++) {
                    $last = $this->rummyRankValue(end($run));
                    $next = $this->rummyRankValue($cards[$i]);
                    if ($next === $last + 1) {
                        $run[] = $cards[$i];
                    } elseif ($next > $last + 1) {
                        break;
                    }
                }
                if (count($run) >= 3 && $this->isValidMeld($run, $cfg)) {
                    $out[] = ['cards'=>$run,'value'=>$this->meldValue($run, $cfg)];
                }
            }
        }
        $unique = [];
        foreach ($out as $candidate) {
            $key = implode('|', $candidate['cards']);
            $unique[$key] = $candidate;
        }
        $out = array_values($unique);
        usort($out, fn($a, $b) => $b['value'] <=> $a['value']);
        return array_slice($out, 0, 12);
    }

    protected function suggestOpeningBatch(array $suggestions, int $opening): array
    {
        $used = [];
        $batch = [];
        $value = 0;
        foreach ($suggestions as $suggestion) {
            $overlap = false;
            foreach ($suggestion['cards'] as $card) {
                if (($used[$card] ?? 0) > 0) {
                    $overlap = true;
                    break;
                }
            }
            if ($overlap) {
                continue;
            }
            $batch[] = $suggestion['cards'];
            foreach ($suggestion['cards'] as $card) {
                $used[$card] = ($used[$card] ?? 0) + 1;
            }
            $value += (int)$suggestion['value'];
            if ($value >= $opening) {
                return $batch;
            }
        }
        return [];
    }

    protected function sortMeldCards(array $cards): array
    {
        usort($cards, fn($a, $b) => [$this->suit($a), $this->rummyRankValue($a)] <=> [$this->suit($b), $this->rummyRankValue($b)]);
        return array_values($cards);
    }

    protected function organize(array $state, string $playerId, string $strategy): array
    {
        $state['hands'][$playerId] = $this->sortCards($state['hands'][$playerId] ?? []);
        $state = $this->record($state, 'hand.organized', compact('playerId','strategy'));
        return $this->finalizeState($state);
    }

    protected function scoreRummyRound(array $state, string $winnerId, bool $meldOut): array
    {
        foreach ($state['players'] as $p) {
            $pid=(string)$p['id'];
            if ($pid === $winnerId) $delta = $meldOut ? -60 : -30;
            else $delta = array_sum(array_map(fn($c)=>min(10,$this->rankValue($c)), $state['hands'][$pid] ?? []));
            $key = ($state['config']['partnership'] ?? false) ? $this->teamOf($state, $pid) : $pid;
            $state['scores'][$key] = ($state['scores'][$key] ?? 0) + $delta;
        }
        $state = $this->record($state, 'rummy.round_scored', ['winner'=>$winnerId,'scores'=>$state['scores']]);
        if (($state['round'] ?? 1) >= (int)($state['config']['rounds'] ?? 5)) { $state['gameOver']=true; $state['winner']=$this->bestScoreKey($state['scores']); }
        else $state = $this->newRoundFromState($state);
        return $state;
    }

    protected function bestScoreKey(array $scores): string|int { asort($scores); return array_key_first($scores); }

    protected function solitaireDraw(array $state, string $playerId): array
    {
        $card = array_shift($state['hands'][$playerId]);
        if (!$card) throw new GameEngineException('لا يوجد ورق في الستوك.');
        $state['discard'][] = ['player'=>$playerId,'card'=>$card];
        $state = $this->record($state, 'solitaire.draw', compact('playerId'));
        return $this->finalizeState($state);
    }

    protected function solitaireFoundation(array $state, string $playerId, string $card): array
    {
        if (!in_array($card, $state['tableau'][$playerId] ?? [], true)) throw new GameEngineException('الورقة ليست على الطاولة.');
        $s=$this->suit($card); $pile=$state['foundation'][$playerId][$s] ?? [];
        $need = count($pile)+1; if ($this->rankValue($card) !== $need) throw new GameEngineException('لا يمكن نقل الورقة إلى الأساس الآن.');
        $this->removeOneCard($state['tableau'][$playerId], $card);
        $state['foundation'][$playerId][$s][] = $card;
        $state['scores'][$playerId] = ($state['scores'][$playerId] ?? 0) + 10;
        $state = $this->record($state, 'solitaire.foundation', compact('playerId','card'));
        return $this->finalizeState($state);
    }

    protected function setAway(array $state, string $playerId, bool $away): array
    {
        foreach ($state['players'] as &$p) if ($p['id']===$playerId) $p['away']=$away;
        $state = $this->record($state, $away?'player.away':'player.returned', compact('playerId'));
        return $this->finalizeState($state);
    }

    public function botMove(array $state): array
    {
        if ($state['gameOver']) return $state;
        $pid = $this->currentPlayerId($state);
        $actions = array_values(array_filter($this->availableActions($state, $pid), fn($a)=>($a['type']??'') !== 'wait'));
        if (!$actions) return $state;
        $choice = $this->chooseBotAction($state, $pid, $actions);
        return $this->applyAction($state, $pid, $choice);
    }

    protected function chooseBotAction(array $state, string $pid, array $actions): array
    {
        foreach ($actions as $a) if (($a['type']??'')==='bid') { $power=$this->handPower($state['hands'][$pid]??[]); if($power>85) return $a; }
        foreach (['choose_trump','choose_contract','meld_batch','meld','lay_off','draw_discard','draw_deck','play_card','discard','draw_stock','move_to_foundation','pass'] as $pref) foreach ($actions as $a) if (($a['type']??'')===$pref) return $a;
        return $actions[0];
    }

    protected function handPower(array $hand): int { return array_sum(array_map(fn($c)=>$this->rankValue($c), $hand)); }

    public function playerView(array $state, string $playerId): array
    {
        $this->assertPlayer($state, $playerId);
        $view = $state;
        foreach ($view['hands'] as $pid=>$hand) if ($pid !== $playerId) $view['hands'][$pid] = ['count'=>count($hand)];
        $view['antiCheat']['serverOnly'] = false;
        return $view;
    }

    public function spectatorView(array $state): array
    {
        $view=$state; foreach($view['hands'] as $pid=>$hand) $view['hands'][$pid]=['count'=>count($hand)]; return $view;
    }

    public function serialize(array $state): string { return json_encode($state, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); }
    public function deserialize(string $json): array { $s=json_decode($json,true); if(!is_array($s)) throw new GameEngineException('حالة غير صالحة'); return $s; }

    protected function finalizeState(array $state): array
    {
        $state['antiCheat']['moveCounter'] = (int)($state['antiCheat']['moveCounter'] ?? 0) + 1;
        $state['antiCheat']['lastHash'] = $this->stateHash($state);
        return $state;
    }
    public function stateHash(array $state): string { $copy=$state; unset($copy['antiCheat']['lastHash']); return hash('sha256', json_encode($copy, JSON_UNESCAPED_UNICODE)); }

    protected function record(array $state, string $type, array $data=[]): array
    {
        $state['events'][] = ['n'=>count($state['events'])+1, 'type'=>$type, 'data'=>$data, 'time'=>date('c')];
        return $state;
    }
    protected function currentPlayerId(array $state): string { return (string)$state['players'][$state['currentIndex']]['id']; }
    protected function advance(array $state): array { $state['currentIndex']=($state['currentIndex']+1)%count($state['players']); return $state; }
    protected function assertPlayer(array $state, string $pid): void { foreach($state['players'] as $p) if($p['id']===$pid) return; throw new GameEngineException('اللاعب غير موجود.'); }
    protected function playerIndex(array $state, string $pid): int { foreach($state['players'] as $i=>$p) if($p['id']===$pid) return $i; throw new GameEngineException('اللاعب غير موجود.'); }
    protected function teamOf(array $state, string $pid): string|int { foreach($state['players'] as $p) if($p['id']===$pid) return $p['team']; return $pid; }
    protected function allHandsEmpty(array $state): bool { foreach($state['hands'] as $h) if(count($h)>0) return false; return true; }

    protected function removeOneCard(array &$cards, string $card): bool
    {
        $index = array_search($card, $cards, true);
        if ($index === false) {
            return false;
        }
        array_splice($cards, (int)$index, 1);
        $cards = array_values($cards);
        return true;
    }

    protected function cardsContained(array $hand, array $requested): bool
    {
        $copy = array_values($hand);
        foreach ($requested as $card) {
            if (!$this->removeOneCard($copy, (string)$card)) {
                return false;
            }
        }
        return true;
    }

    protected function rummyRankValue(string $card): int
    {
        if (str_starts_with($card, 'JOKER')) {
            return 0;
        }
        return ['A'=>14,'K'=>13,'Q'=>12,'J'=>11,'10'=>10,'9'=>9,'8'=>8,'7'=>7,'6'=>6,'5'=>5,'4'=>4,'3'=>3,'2'=>2][$this->rank($card)] ?? 0;
    }

    protected function rank(string $card): string { return explode('_',$card)[0] ?? $card; }
    protected function suit(string $card): string { return explode('_',$card)[1] ?? ''; }
    protected function rankValue(string $card, ?string $mode=null, ?string $trump=null): int
    {
        if (str_starts_with($card,'JOKER')) return 20;
        $rank=$this->rank($card); $map=['A'=>14,'K'=>13,'Q'=>12,'J'=>11,'10'=>10,'9'=>9,'8'=>8,'7'=>7,'6'=>6,'5'=>5,'4'=>4,'3'=>3,'2'=>2];
        if ($mode==='baloot') { $s=$this->suit($card); if($trump && $s===$trump) $map=['J'=>20,'9'=>19,'A'=>18,'10'=>17,'K'=>16,'Q'=>15,'8'=>8,'7'=>7]; else $map=['A'=>14,'10'=>13,'K'=>12,'Q'=>11,'J'=>10,'9'=>9,'8'=>8,'7'=>7]; }
        return $map[$rank] ?? 0;
    }
    protected function sortCards(array $cards): array
    {
        usort($cards, function (string $a, string $b): int {
            $suit = $this->suit($a) <=> $this->suit($b);
            return $suit !== 0 ? $suit : ($this->rummyRankValue($a) <=> $this->rummyRankValue($b));
        });
        return array_values($cards);
    }
}
