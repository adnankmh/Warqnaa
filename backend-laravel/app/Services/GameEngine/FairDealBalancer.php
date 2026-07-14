<?php

namespace App\Services\GameEngine;

/**
 * Post-shuffle deal balancer.
 *
 * The deck is shuffled first with a cryptographically secure source. This
 * service only performs bounded, symmetric swaps between already-dealt hands:
 * it never targets a username/seat, never creates/removes a card and never
 * exposes the shuffle seed. The goal is to avoid one unusably weak hand while
 * keeping normal variation and skill meaningful.
 */
final class FairDealBalancer
{
    /** @var array<string,int> */
    private const RANK_VALUES = [
        '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7,
        '8'=>8, '9'=>9, '10'=>10, 'J'=>11, 'Q'=>12, 'K'=>13, 'A'=>14,
        'JOKER'=>17, 'JOKER_R'=>17, 'JOKER_B'=>17,
    ];

    /**
     * @param array<string|int,array<int,string>> $hands
     * @return array<string|int,array<int,string>>
     */
    public static function balance(array $hands, string $mode = 'trick'): array
    {
        if (count($hands) < 2) {
            return $hands;
        }

        $keys = array_keys($hands);
        $handSize = max(1, min(array_map('count', $hands)));
        $premiumQuota = $handSize >= 13 ? 2 : ($handSize >= 7 ? 1 : 0);
        $maxSpread = match ($mode) {
            'rummy', 'hand', 'banakil' => max(18, (int) round($handSize * 2.0)),
            'baloot' => 16,
            default => max(16, (int) round($handSize * 1.75)),
        };

        // A hard iteration cap guarantees predictable latency even for multi-deck games.
        for ($step = 0; $step < 96; $step++) {
            $metrics = [];
            foreach ($keys as $key) {
                $metrics[$key] = self::metrics($hands[$key], $mode);
            }

            $weak = $keys[0];
            $strong = $keys[0];
            foreach ($keys as $key) {
                if ($metrics[$key]['score'] < $metrics[$weak]['score']) $weak = $key;
                if ($metrics[$key]['score'] > $metrics[$strong]['score']) $strong = $key;
            }

            $spread = $metrics[$strong]['score'] - $metrics[$weak]['score'];
            $needsPremium = $metrics[$weak]['premium'] < $premiumQuota;
            if (!$needsPremium && $spread <= $maxSpread) {
                break;
            }

            $swap = self::bestSwap(
                $hands[$strong],
                $hands[$weak],
                $metrics[$strong],
                $metrics[$weak],
                $premiumQuota,
                $mode
            );
            if ($swap === null) {
                break;
            }

            [$strongIndex, $weakIndex] = $swap;
            [$hands[$strong][$strongIndex], $hands[$weak][$weakIndex]] = [
                $hands[$weak][$weakIndex],
                $hands[$strong][$strongIndex],
            ];
            $hands[$strong] = array_values($hands[$strong]);
            $hands[$weak] = array_values($hands[$weak]);
        }

        return $hands;
    }

    /**
     * @param array<int,string> $donor
     * @param array<int,string> $receiver
     * @param array{score:int,premium:int} $donorMetrics
     * @param array{score:int,premium:int} $receiverMetrics
     * @return array{int,int}|null
     */
    private static function bestSwap(
        array $donor,
        array $receiver,
        array $donorMetrics,
        array $receiverMetrics,
        int $premiumQuota,
        string $mode
    ): ?array {
        $beforeGap = abs($donorMetrics['score'] - $receiverMetrics['score']);
        $best = null;
        $bestGain = 0;

        foreach ($donor as $di => $give) {
            $givePremium = self::isPremium($give);
            if ($givePremium && $donorMetrics['premium'] <= $premiumQuota) continue;

            foreach ($receiver as $ri => $take) {
                if (self::cardScore($give, $mode) <= self::cardScore($take, $mode)) continue;

                $donorCopy = $donor;
                $receiverCopy = $receiver;
                $donorCopy[$di] = $take;
                $receiverCopy[$ri] = $give;
                $newDonor = self::metrics($donorCopy, $mode);
                $newReceiver = self::metrics($receiverCopy, $mode);
                if ($newDonor['premium'] < $premiumQuota) continue;

                $afterGap = abs($newDonor['score'] - $newReceiver['score']);
                $premiumGain = ($newReceiver['premium'] - $receiverMetrics['premium']) * 12;
                $gain = ($beforeGap - $afterGap) + $premiumGain;
                if ($gain > $bestGain) {
                    $bestGain = $gain;
                    $best = [$di, $ri];
                }
            }
        }

        return $best;
    }

    /** @param array<int,string> $hand @return array{score:int,premium:int} */
    private static function metrics(array $hand, string $mode): array
    {
        $score = 0;
        $premium = 0;
        $rankCounts = [];
        $suitRanks = [];

        foreach ($hand as $card) {
            $score += self::cardScore($card, $mode);
            if (self::isPremium($card)) $premium++;
            [$rank, $suit] = self::parts($card);
            $rankCounts[$rank] = ($rankCounts[$rank] ?? 0) + 1;
            if ($suit !== '') $suitRanks[$suit][] = self::rankValue($rank);
        }

        if (in_array($mode, ['rummy', 'hand', 'banakil'], true)) {
            foreach ($rankCounts as $count) {
                if ($count >= 2) $score += ($count - 1) * 4;
            }
            foreach ($suitRanks as $ranks) {
                $ranks = array_values(array_unique($ranks));
                sort($ranks);
                for ($i = 1; $i < count($ranks); $i++) {
                    if ($ranks[$i] - $ranks[$i - 1] === 1) $score += 3;
                }
            }
        }

        return ['score'=>$score, 'premium'=>$premium];
    }

    private static function cardScore(string $card, string $mode): int
    {
        [$rank] = self::parts($card);
        $value = self::rankValue($rank);
        if (str_starts_with($rank, 'JOKER')) return 22;
        if ($mode === 'baloot') {
            return match ($rank) {
                'J' => 18, '9' => 17, 'A' => 16, '10' => 15,
                'K' => 13, 'Q' => 12, default => $value,
            };
        }
        return $value + ($value >= 11 ? 2 : 0);
    }

    private static function isPremium(string $card): bool
    {
        [$rank] = self::parts($card);
        return in_array($rank, ['A','K','Q','J','JOKER','JOKER_R','JOKER_B'], true);
    }

    /** @return array{string,string} */
    private static function parts(string $card): array
    {
        if (str_starts_with($card, 'JOKER')) return [$card, 'joker'];
        $parts = preg_split('/[_\-\s]+/', trim($card)) ?: [];
        if (count($parts) >= 2) return [strtoupper((string)$parts[0]), strtoupper((string)$parts[1])];
        if (preg_match('/^(10|[2-9AJQK])([CDSH])$/i', trim($card), $m)) {
            return [strtoupper($m[1]), strtoupper($m[2])];
        }
        return [strtoupper(trim($card)), ''];
    }

    private static function rankValue(string $rank): int
    {
        return self::RANK_VALUES[$rank] ?? 0;
    }
}
