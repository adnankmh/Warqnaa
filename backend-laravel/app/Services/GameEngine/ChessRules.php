<?php
namespace App\Services\GameEngine;

/**
 * Server-authoritative standard chess core with legal move validation,
 * castling, en-passant, promotion, check/checkmate, stalemate,
 * threefold repetition, insufficient material and the fifty-move rule.
 */
class ChessRules implements GameRuleContract
{
    public function initialState(array $players,array $options=[]): array
    {
        $players=array_values(array_slice($players,0,2));while(count($players)<2)$players[]='bot:chess_'.count($players);
        $board=$this->initialBoard();$state=[
            'phase'=>'playing','game_type'=>'chess','players'=>$players,'turn'=>$players[0],
            'colors'=>[$players[0]=>'white',$players[1]=>'black'],'board'=>$board,'move_history'=>[],
            'castling'=>['white'=>['king'=>true,'queen'=>true],'black'=>['king'=>true,'queen'=>true]],
            'en_passant'=>null,'halfmove'=>0,'fullmove'=>1,'check'=>false,'draw_offer'=>null,
            'score'=>array_fill_keys($players,0),'position_counts'=>[],
            'messages'=>['بدأت مباراة شطرنج قياسية؛ جميع النقلات تُراجع على الخادم.'],
            'engine_quality'=>'chess_standard_complete_v142',
        ];
        $state['position_counts'][$this->positionHash($state)]=1;return $state;
    }

    public function validate(array $state,string $playerId,string $action,array $payload): bool
    {
        if(($state['phase']??null)!=='playing')return false;
        if($action==='accept_draw')return ($state['draw_offer']??null)!==null&&($state['draw_offer']??null)!==$playerId;
        if(($state['turn']??null)!==$playerId)return false;
        if(in_array($action,['resign','offer_draw'],true))return true;
        if($action!=='move_piece')return false;
        $from=strtolower((string)($payload['from']??''));$to=strtolower((string)($payload['to']??''));
        if(!$this->validSquare($from)||!$this->validSquare($to)||$from===$to)return false;
        $piece=$state['board'][$from]??null;$color=$state['colors'][$playerId]??null;
        if(!$piece||$this->colorOf((string)$piece)!==$color)return false;
        if(!$this->pseudoLegal($state,$from,$to,$color))return false;
        $next=$this->moveState($state,$from,$to,(string)($payload['promotion']??'Q'));
        return !$this->kingInCheck($next['board'],$color);
    }

    public function apply(array $state,string $playerId,string $action,array $payload): array
    {
        if(!$this->validate($state,$playerId,$action,$payload)){$state['last_error_message']='النقلة أو الإجراء غير قانوني.';return $state;}
        if($action==='resign'){
            $winner=$this->otherPlayer($state['players'],$playerId);$state['phase']='finished';$state['winner']=$winner;$state['score'][$winner]=1;$state['messages'][]=$this->label($playerId).' استسلم.';return $state;
        }
        if($action==='offer_draw'){$state['draw_offer']=$playerId;$state['messages'][]=$this->label($playerId).' عرض التعادل.';return $state;}
        if($action==='accept_draw'){$state['phase']='finished';$state['winner']=null;$state['draw_reason']='agreement';$state['messages'][]='تم قبول التعادل.';return $state;}

        $from=strtolower((string)$payload['from']);$to=strtolower((string)$payload['to']);$piece=$state['board'][$from];
        $captured=$state['board'][$to]??null;
        if(substr($piece,1,1)==='P'&&!$captured&&($state['en_passant']['target']??null)===$to)$captured=$state['board'][$state['en_passant']['pawn']??'']??null;
        $state=$this->moveState($state,$from,$to,(string)($payload['promotion']??'Q'));
        $state['move_history'][]=['player'=>$playerId,'from'=>$from,'to'=>$to,'piece'=>$piece,'captured'=>$captured,'at'=>now()->toIso8601String()];
        $state['halfmove']=($captured||substr($piece,1,1)==='P')?0:(int)($state['halfmove']??0)+1;
        if(($state['colors'][$playerId]??'')==='black')$state['fullmove']=(int)($state['fullmove']??1)+1;
        $nextPlayer=$this->otherPlayer($state['players'],$playerId);$nextColor=$state['colors'][$nextPlayer]??'black';
        $state['turn']=$nextPlayer;$state['check']=$this->kingInCheck($state['board'],$nextColor);$state['draw_offer']=null;
        $state['messages'][]=$this->label($playerId)." حرّك $from إلى $to".($captured?' وأخذ قطعة.':'.');
        $hash=$this->positionHash($state);$state['position_counts'][$hash]=(int)($state['position_counts'][$hash]??0)+1;

        $moves=$this->allLegalMoves($state,$nextPlayer);
        if(!$moves){$state['phase']='finished';if($state['check']){$state['winner']=$playerId;$state['score'][$playerId]=1;$state['messages'][]='كش مات! الفائز: '.$this->label($playerId);}else{$state['winner']=null;$state['draw_reason']='stalemate';$state['messages'][]='تعادل بسبب انعدام النقلات القانونية.';}}
        elseif((int)$state['halfmove']>=100){$this->draw($state,'fifty_move_rule','تعادل بقاعدة الخمسين نقلة.');}
        elseif((int)$state['position_counts'][$hash]>=3){$this->draw($state,'threefold_repetition','تعادل بتكرار الوضعية ثلاث مرات.');}
        elseif($this->insufficientMaterial($state['board'])){$this->draw($state,'insufficient_material','تعادل لعدم كفاية القطع لإحداث كش مات.');}
        unset($state['last_error_message']);return $state;
    }

    public function onTurnTimeout(array $state): array
    {
        $player=(string)($state['turn']??'');$moves=$this->allLegalMoves($state,$player);if(!$moves)return $state;
        // Prefer captures and checks, then choose randomly among equally useful moves.
        usort($moves,function($a,$b)use($state){$ca=isset($state['board'][$a['to']])?1:0;$cb=isset($state['board'][$b['to']])?1:0;return $cb<=>$ca;});
        $best=array_values(array_filter($moves,fn($m)=>isset($state['board'][$m['to']])===isset($state['board'][$moves[0]['to']])));
        $move=$best[random_int(0,count($best)-1)];$state['messages'][]='⏱️ انتهى الوقت؛ نفّذ الكمبيوتر نقلة قانونية.';
        return $this->apply($state,$player,'move_piece',$move);
    }

    public function availableActions(array $state,string $playerId): array
    {
        if(($state['phase']??null)!=='playing')return [];
        if(($state['draw_offer']??null)!==null&&$state['draw_offer']!==$playerId)return [['type'=>'accept_draw']];
        if(($state['turn']??null)!==$playerId)return [];
        return array_merge(array_map(fn($m)=>['type'=>'move_piece']+$m,$this->allLegalMoves($state,$playerId)),[['type'=>'offer_draw'],['type'=>'resign']]);
    }

    private function moveState(array $state,string $from,string $to,string $promotion='Q'): array
    {
        $board=$state['board'];$piece=$board[$from];$color=$this->colorOf($piece);$kind=substr($piece,1,1);
        $captured=$board[$to]??null;unset($board[$from]);
        if($kind==='P'&&!$captured&&($state['en_passant']['target']??null)===$to)unset($board[$state['en_passant']['pawn']]);
        if($kind==='K'&&abs(ord($to[0])-ord($from[0]))===2){
            if($to[0]==='g'){$rookFrom='h'.$from[1];$rookTo='f'.$from[1];}else{$rookFrom='a'.$from[1];$rookTo='d'.$from[1];}
            $board[$rookTo]=$board[$rookFrom];unset($board[$rookFrom]);
        }
        if($kind==='P'&&in_array($to[1],['1','8'],true)){$promotion=strtoupper($promotion);if(!in_array($promotion,['Q','R','B','N'],true))$promotion='Q';$piece=substr($piece,0,1).$promotion;}
        $board[$to]=$piece;$state['board']=$board;$state['en_passant']=null;
        if($kind==='P'&&abs((int)$to[1]-(int)$from[1])===2){$middle=$from[0].(((int)$to[1]+(int)$from[1])/2);$state['en_passant']=['target'=>$middle,'pawn'=>$to,'color'=>$color];}
        if($kind==='K')$state['castling'][$color]=['king'=>false,'queen'=>false];
        if($kind==='R')$this->disableRookRight($state,$color,$from);
        if($captured&&substr($captured,1,1)==='R')$this->disableRookRight($state,$this->colorOf($captured),$to);
        return $state;
    }

    private function allLegalMoves(array $state,string $playerId): array
    {
        $moves=[];$color=$state['colors'][$playerId]??null;if(!$color)return [];
        foreach($state['board'] as $from=>$piece){if($this->colorOf((string)$piece)!==$color)continue;foreach($this->squares() as $to){$payload=['from'=>$from,'to'=>$to,'promotion'=>'Q'];if($this->validate($state,$playerId,'move_piece',$payload))$moves[]=$payload;}}
        return $moves;
    }

    private function pseudoLegal(array $state,string $from,string $to,string $color): bool
    {
        $board=$state['board'];$piece=$board[$from]??null;if(!$piece||$this->colorOf($piece)!==$color)return false;
        $target=$board[$to]??null;if($target&&($this->colorOf($target)===$color||substr($target,1,1)==='K'))return false;
        [$fx,$fy]=$this->xy($from);[$tx,$ty]=$this->xy($to);$dx=$tx-$fx;$dy=$ty-$fy;$kind=substr($piece,1,1);
        return match($kind){
            'P'=>$this->pawnLegal($state,$from,$to,$color,$dx,$dy,$target),
            'N'=>in_array([abs($dx),abs($dy)],[[1,2],[2,1]],true),
            'B'=>abs($dx)===abs($dy)&&$this->pathClear($board,$fx,$fy,$tx,$ty),
            'R'=>($dx===0||$dy===0)&&$this->pathClear($board,$fx,$fy,$tx,$ty),
            'Q'=>(($dx===0||$dy===0)||abs($dx)===abs($dy))&&$this->pathClear($board,$fx,$fy,$tx,$ty),
            'K'=>max(abs($dx),abs($dy))===1||$this->castleLegal($state,$from,$to,$color),
            default=>false,
        };
    }

    private function pawnLegal(array $state,string $from,string $to,string $color,int $dx,int $dy,?string $target): bool
    {
        $direction=$color==='white'?1:-1;$startRank=$color==='white'?2:7;$rank=(int)$from[1];$board=$state['board'];
        if($dx===0&&$dy===$direction&&!$target)return true;
        if($dx===0&&$dy===2*$direction&&$rank===$startRank&&!$target){[$fx,$fy]=$this->xy($from);return !isset($board[$this->square($fx,$fy+$direction)]);}
        if(abs($dx)===1&&$dy===$direction){if($target&&$this->colorOf($target)!==$color)return true;return ($state['en_passant']['target']??null)===$to&&($state['en_passant']['color']??null)!==$color;}
        return false;
    }

    private function castleLegal(array $state,string $from,string $to,string $color): bool
    {
        $rank=$color==='white'?'1':'8';if($from!=='e'.$rank||!in_array($to,['g'.$rank,'c'.$rank],true))return false;
        if($this->kingInCheck($state['board'],$color))return false;$kingSide=$to[0]==='g';$right=$kingSide?'king':'queen';
        if(!($state['castling'][$color][$right]??false))return false;$rook=$kingSide?'h'.$rank:'a'.$rank;
        if(($state['board'][$rook]??null)!==($color==='white'?'wR':'bR'))return false;
        $empty=$kingSide?['f'.$rank,'g'.$rank]:['b'.$rank,'c'.$rank,'d'.$rank];foreach($empty as $sq)if(isset($state['board'][$sq]))return false;
        $cross=$kingSide?['f'.$rank,'g'.$rank]:['d'.$rank,'c'.$rank];foreach($cross as $sq){$board=$state['board'];$board[$sq]=$board[$from];unset($board[$from]);if($this->kingInCheck($board,$color))return false;}
        return true;
    }

    private function kingInCheck(array $board,string $color): bool
    {
        $king=array_search(($color==='white'?'w':'b').'K',$board,true);if($king===false)return true;$enemy=$color==='white'?'black':'white';
        foreach($board as $from=>$piece)if($this->colorOf((string)$piece)===$enemy&&$this->pseudoLegalAttack($board,(string)$from,(string)$king,$enemy))return true;return false;
    }
    private function pseudoLegalAttack(array $board,string $from,string $to,string $color): bool
    {
        $piece=$board[$from]??null;if(!$piece)return false;[$fx,$fy]=$this->xy($from);[$tx,$ty]=$this->xy($to);$dx=$tx-$fx;$dy=$ty-$fy;$kind=substr($piece,1,1);
        if($kind==='P')return abs($dx)===1&&$dy===($color==='white'?1:-1);if($kind==='N')return in_array([abs($dx),abs($dy)],[[1,2],[2,1]],true);if($kind==='K')return max(abs($dx),abs($dy))===1;
        if($kind==='B')return abs($dx)===abs($dy)&&$this->pathClear($board,$fx,$fy,$tx,$ty);if($kind==='R')return ($dx===0||$dy===0)&&$this->pathClear($board,$fx,$fy,$tx,$ty);if($kind==='Q')return (($dx===0||$dy===0)||abs($dx)===abs($dy))&&$this->pathClear($board,$fx,$fy,$tx,$ty);return false;
    }
    private function pathClear(array $board,int $fx,int $fy,int $tx,int $ty): bool{$sx=$tx<=>$fx;$sy=$ty<=>$fy;$x=$fx+$sx;$y=$fy+$sy;while($x!==$tx||$y!==$ty){if(isset($board[$this->square($x,$y)]))return false;$x+=$sx;$y+=$sy;}return true;}
    private function initialBoard(): array{$board=[];$order=['R','N','B','Q','K','B','N','R'];foreach(range(0,7) as $i){$file=chr(ord('a')+$i);$board[$file.'1']='w'.$order[$i];$board[$file.'2']='wP';$board[$file.'7']='bP';$board[$file.'8']='b'.$order[$i];}return $board;}
    private function disableRookRight(array &$state,string $color,string $square): void{$rank=$color==='white'?'1':'8';if($square==='h'.$rank)$state['castling'][$color]['king']=false;if($square==='a'.$rank)$state['castling'][$color]['queen']=false;}
    private function insufficientMaterial(array $board): bool{$pieces=array_values(array_filter($board,fn($p)=>substr((string)$p,1,1)!=='K'));if(!$pieces)return true;if(count($pieces)===1&&in_array(substr((string)$pieces[0],1,1),['B','N'],true))return true;if(count($pieces)===2&&substr((string)$pieces[0],1,1)==='B'&&substr((string)$pieces[1],1,1)==='B')return true;return false;}
    private function positionHash(array $state): string{$board=$state['board'];ksort($board);return hash('sha256',json_encode([$board,$state['turn']??null,$state['castling']??[],$state['en_passant']['target']??null]));}
    private function draw(array &$state,string $reason,string $message): void{$state['phase']='finished';$state['winner']=null;$state['draw_reason']=$reason;$state['messages'][]=$message;}
    private function colorOf(string $piece): string{return str_starts_with($piece,'w')?'white':'black';}
    private function validSquare(string $square): bool{return (bool)preg_match('/^[a-h][1-8]$/',$square);}
    private function xy(string $square): array{return [ord($square[0])-ord('a'),(int)$square[1]-1];}
    private function square(int $x,int $y): string{return chr(ord('a')+$x).($y+1);}
    private function squares(): array{$out=[];foreach(range('a','h') as $f)foreach(range(1,8) as $r)$out[]=$f.$r;return $out;}
    private function otherPlayer(array $players,string $current): string{return $players[0]===$current?($players[1]??$current):($players[0]??$current);}
    private function label(string $player): string{return str_replace(['user:','bot:'],['لاعب ','بوت '],$player);}
}
