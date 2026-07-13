<?php

namespace App\Services\WarqnaPro;

use App\Models\{ChallengeDefinition,ChallengeProgress,User};
use App\Services\Leveling\XpService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChallengeService
{
    public function __construct(private readonly WalletService $wallet, private readonly XpService $xp) {}

    public function periodKey(ChallengeDefinition $definition): string
    {
        return match ($definition->cadence) {
            'weekly' => now()->format('o-\WW'),
            'seasonal' => now()->format('Y-m'),
            default => now()->toDateString(),
        };
    }

    /** @return array<int,array<string,mixed>> */
    public function center(User $user): array
    {
        return ChallengeDefinition::where('active', true)->orderBy('sort_order')->get()
            ->map(fn (ChallengeDefinition $definition) => $this->payload($user, $definition))->values()->all();
    }

    /** @return array<string,mixed> */
    public function activate(User $user, string $key): array
    {
        $definition = ChallengeDefinition::where('key', $key)->where('active', true)->firstOrFail();
        ChallengeProgress::firstOrCreate([
            'user_id'=>$user->id,'challenge_definition_id'=>$definition->id,'period_key'=>$this->periodKey($definition),
        ], ['progress'=>0,'payload'=>['activated_at'=>now()->toIso8601String()]]);
        return $this->payload($user, $definition);
    }

    public function record(User $user, string $metric, int $amount = 1): void
    {
        if ($amount <= 0) return;
        ChallengeDefinition::where('active', true)->where('metric', $metric)->get()->each(function (ChallengeDefinition $definition) use ($user, $amount) {
            $period = $this->periodKey($definition);
            $progress = ChallengeProgress::firstOrCreate([
                'user_id'=>$user->id,'challenge_definition_id'=>$definition->id,'period_key'=>$period,
            ], ['progress'=>0,'payload'=>['activated_at'=>now()->toIso8601String(),'automatic'=>true]]);
            if ($progress->claimed_at) return;
            $progress->progress = min((int)$definition->target, (int)$progress->progress + $amount);
            $progress->save();
        });
    }

    /** @return array<string,mixed> */
    public function claim(User $user, string $key): array
    {
        return DB::transaction(function () use ($user, $key) {
            $definition = ChallengeDefinition::where('key', $key)->where('active', true)->lockForUpdate()->firstOrFail();
            $progress = ChallengeProgress::where('user_id',$user->id)
                ->where('challenge_definition_id',$definition->id)
                ->where('period_key',$this->periodKey($definition))->lockForUpdate()->first();
            if (!$progress || (int)$progress->progress < (int)$definition->target) throw new RuntimeException('لم يكتمل التحدي بعد.');
            if ($progress->claimed_at) throw new RuntimeException('تم استلام مكافأة هذا التحدي مسبقاً.');
            if ((int)$definition->reward_tokens > 0) $this->wallet->credit($user, (int)$definition->reward_tokens, 'challenge_reward', ['challenge'=>$definition->key,'period'=>$progress->period_key]);
            if ((int)$definition->reward_xp > 0) $this->xp->award($user, (int)$definition->reward_xp, 0, false, false, false);
            $progress->claimed_at = now();
            $progress->payload = array_merge((array)$progress->payload, ['claimed_at'=>now()->toIso8601String()]);
            $progress->save();
            return $this->payload($user, $definition) + ['reward_tokens'=>(int)$definition->reward_tokens,'reward_xp'=>(int)$definition->reward_xp];
        });
    }

    /** @return array<string,mixed> */
    private function payload(User $user, ChallengeDefinition $definition): array
    {
        $period = $this->periodKey($definition);
        $progress = ChallengeProgress::where('user_id',$user->id)->where('challenge_definition_id',$definition->id)->where('period_key',$period)->first();
        $value = (int)($progress?->progress ?? 0);
        $name = (array)$definition->name;
        $description = (array)($definition->description ?? []);
        return [
            'key'=>$definition->key,'name'=>$name,'description'=>$description,
            'name_ar'=>$name['ar'] ?? $name['en'] ?? $definition->key,
            'description_ar'=>$description['ar'] ?? $description['en'] ?? '',
            'icon'=>data_get($definition->settings,'icon',match($definition->cadence){'daily'=>'⚡','weekly'=>'🏆',default=>'🐉'}),
            'cadence'=>$definition->cadence,'metric'=>$definition->metric,'period_key'=>$period,
            'progress'=>$value,'target'=>(int)$definition->target,'activated'=>(bool)$progress,
            'completed'=>$value >= (int)$definition->target,'claimed'=>(bool)$progress?->claimed_at,
            'reward_tokens'=>(int)$definition->reward_tokens,'reward_xp'=>(int)$definition->reward_xp,
        ];
    }
}
