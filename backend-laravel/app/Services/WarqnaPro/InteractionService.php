<?php
namespace App\Services\WarqnaPro;

class InteractionService
{
    public function price(string $key): int
    {
        return (int)(config('warqna_economy_matrix.throwables.'.$key.'.cost') ?? 0);
    }

    public function payload(string $key, string $from, string $to): array
    {
        $item=config('warqna_economy_matrix.throwables.'.$key,[]);
        return [
            'key'=>$key,'icon'=>$item['icon'] ?? '✨','name'=>$item['ar'] ?? $key,
            'from'=>$from,'to'=>$to,'cost'=>$this->price($key),'at'=>now()->toIso8601String()
        ];
    }
}
