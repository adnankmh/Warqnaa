<?php
namespace App\Services\GameEngine;

class GameFactory
{
    public static function make(string $key): GameRuleContract
    {
        return match($key){
            // Base Tarneeb stays on v132 standalone Tarneeb adapter.
            'tarneeb','tarneeb_41','tarneeb_61' => new TarneebRules(),

            // v133: uploaded global engines final-v1.
            'syrian_tarneeb','tarneeb_400','400',
            'hand','hand_partner','saudi_hand','pinochle','banakil','solitaire_multiplayer',
            'trix','trix_partner','trix_complex','baloot' => new GlobalCardEngineRules($key),

            // Dedicated rule engines already present in the Laravel platform.
            'domino' => new DominoRules(),
            'basra' => new BasraRules(),
            'backgammon' => new BackgammonRules(),
            'jackaroo' => new JackarooRules(),
            'chess' => new ChessRules(),

            default => new UniversalSocialGameRules($key),
        };
    }
}
