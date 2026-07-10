<?php
namespace App\Services\GameEngine; interface GameRuleContract { public function initialState(array $players, array $options=[]): array; public function validate(array $state, string $playerId, string $action, array $payload): bool; public function apply(array $state, string $playerId, string $action, array $payload): array; }
