<?php
namespace App\Services\GameEngine;

class ContractTrixRules extends GenericTrickTakingRules
{
    use UnifiedEngineHelpers;
    public function __construct(private string $variant='trix') { parent::__construct($variant); }

    public function initialState(array $players, array $options=[]): array
    {
        $state=parent::initialState($players,$options);
        $state['phase']='contract_select';
        $state['contracts']=['king_hearts','girls','diamonds','slaps','trix'];
        $state['current_contract']=$options['contract'] ?? 'slaps';
        $state['kingdom_owner']=$players[0] ?? null;
        $state['doubled_cards']=[];
        $state['messages'][]='تريكس: اختر العقد، ثم تبدأ الجولة مع حساب العقوبات والتدبيل.';
        return $state;
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['phase']??'')==='contract_select') return $playerId===($state['kingdom_owner']??null) && $action==='select_contract' && in_array($payload['contract']??'', $state['contracts']??[], true);
        if(($state['phase']??'')==='doubling') return in_array($action,['double','skip_double'],true);
        return parent::validate($state,$playerId,$action,$payload);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(($state['phase']??'')==='contract_select' && $this->validate($state,$playerId,$action,$payload)){
            $state['current_contract']=$payload['contract']; $state['phase']='doubling'; $state['turn']=$this->nextPlayer($state['players'],$playerId); $state['messages'][]='تم اختيار العقد: '.$payload['contract'];
            return $state;
        }
        if(($state['phase']??'')==='doubling'){
            if($action==='double' && !empty($payload['card'])) $state['doubled_cards'][$payload['card']]=$playerId;
            $state['phase']='playing'; $state['turn']=$state['players'][0]??$playerId; return $state;
        }
        $before=$state['last_trick']??[];
        $state=parent::apply($state,$playerId,$action,$payload);
        if(!empty($state['last_trick']) && $state['last_trick']!==$before) $state=$this->scorePenalty($state);
        return $state;
    }

    private function scorePenalty(array $state): array
    {
        $winner=$state['turn']??null; $contract=$state['current_contract']??'slaps'; $penalty=0;
        foreach($state['last_trick'] as $card){
            if($contract==='slaps') $penalty-=15;
            if($contract==='diamonds' && $this->suit($card)==='diamonds') $penalty-=10;
            if($contract==='girls' && $this->rank($card)==='Q') $penalty-=25;
            if($contract==='king_hearts' && $this->suit($card)==='hearts' && $this->rank($card)==='K') $penalty-=75;
        }
        if($winner) $state['score'][$winner]=($state['score'][$winner]??0)+$penalty;
        return $state;
    }
}
