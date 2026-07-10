<?php
namespace App\Services\WarqnaPro;

class EloRatingService
{
    public function expected(int $playerElo, int $opponentElo): float
    {
        return 1 / (1 + pow(10, ($opponentElo - $playerElo) / 400));
    }

    public function calculate(int $playerElo, int $opponentElo, bool $winner, int $k = 32): int
    {
        $actual = $winner ? 1 : 0;
        return (int) round($playerElo + $k * ($actual - $this->expected($playerElo, $opponentElo)));
    }

    public function rankName(int $elo): string
    {
        return match (true) {
            $elo >= 2400 => 'Grand Master',
            $elo >= 2100 => 'Master',
            $elo >= 1800 => 'Diamond',
            $elo >= 1500 => 'Gold',
            $elo >= 1200 => 'Silver',
            default => 'Bronze',
        };
    }
}
