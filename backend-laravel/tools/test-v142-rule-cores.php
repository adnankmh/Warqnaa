<?php
// Standalone rule-core test; Composer/Laravel is not required.
if (!function_exists('now')) {
    function now(): object { return new class { public function toIso8601String(): string { return gmdate('c'); } }; }
}
$base = dirname(__DIR__) . '/app/Services/GameEngine';
foreach (['GameRuleContract.php','Card.php','DeckFactory.php','AbstractCardRules.php','DominoRules.php','BasraRules.php','BackgammonRules.php','JackarooRules.php','ChessRules.php'] as $file) require_once $base.'/'.$file;

use App\Services\GameEngine\{BackgammonRules,BasraRules,ChessRules,DominoRules,JackarooRules};

function check(bool $condition,string $message): void { if(!$condition) throw new RuntimeException($message); echo "[PASS] $message\n"; }
$players=['user:1','bot:1','bot:2','bot:3'];

$domino=(new DominoRules())->initialState(array_slice($players,0,2));
$all=array_merge(...array_merge(array_values($domino['hands']),[$domino['boneyard']]));
check(count($all)===28&&count(array_unique($all))===28,'Domino uses 28 unique tiles');

$basraEngine=new BasraRules();$basra=$basraEngine->initialState(array_slice($players,0,2));
check(count($basra['hands']['user:1'])===4&&count($basra['table'])===4,'Basra deals four cards and opens four');
check(!array_filter($basra['table'],fn($c)=>str_starts_with($c,'J_')||$c==='7_diamonds'),'Basra initial table excludes J and 7 diamonds');
$basra['turn']='user:1';$basra['hands']['user:1']=['J_clubs'];$basra['table']=['2_clubs','3_hearts','K_spades'];
$basra=$basraEngine->apply($basra,'user:1','play_card',['card'=>'J_clubs']);
check(empty($basra['table']),'Basra Jack sweeps the table');

$bgEngine=new BackgammonRules();$bg=$bgEngine->initialState(array_slice($players,0,2));$bg['moves_left']=[1];$bg['dice']=[1,2];
check($bgEngine->validate($bg,'user:1','move',['from'=>1,'to'=>2]),'Backgammon validates direction and die distance');
$bg['bar']['user:1']=1;
check(!$bgEngine->validate($bg,'user:1','move',['from'=>1,'to'=>2]),'Backgammon enforces bar-entry priority');
check($bgEngine->validate($bg,'user:1','move',['from'=>0,'to'=>1]),'Backgammon permits legal bar entry');

$chessEngine=new ChessRules();$chess=$chessEngine->initialState(array_slice($players,0,2));
foreach([
 ['user:1','e2','e4'],['bot:1','a7','a6'],['user:1','g1','f3'],['bot:1','a6','a5'],['user:1','f1','e2'],['bot:1','a5','a4'],
] as [$p,$from,$to]){$chess=$chessEngine->apply($chess,$p,'move_piece',['from'=>$from,'to'=>$to]);check(!isset($chess['last_error_message']),"Chess move $from-$to legal");}
check($chessEngine->validate($chess,'user:1','move_piece',['from'=>'e1','to'=>'g1']),'Chess supports legal king-side castling');
$chess=$chessEngine->apply($chess,'user:1','move_piece',['from'=>'e1','to'=>'g1']);
check(($chess['board']['g1']??null)==='wK'&&($chess['board']['f1']??null)==='wR','Chess castling moves king and rook');

$chess=$chessEngine->initialState(array_slice($players,0,2));
foreach([
 ['user:1','e2','e4'],['bot:1','a7','a6'],['user:1','e4','e5'],['bot:1','d7','d5'],
] as [$p,$from,$to])$chess=$chessEngine->apply($chess,$p,'move_piece',['from'=>$from,'to'=>$to]);
check($chessEngine->validate($chess,'user:1','move_piece',['from'=>'e5','to'=>'d6']),'Chess supports en-passant');
$chess=$chessEngine->apply($chess,'user:1','move_piece',['from'=>'e5','to'=>'d6']);
check(!isset($chess['board']['d5'])&&($chess['board']['d6']??null)==='wP','En-passant removes the passed pawn');

$jackEngine=new JackarooRules();$jack=$jackEngine->initialState($players);$jack['turn']='user:1';$jack['hands']['user:1']=['A_hearts'];
$actions=$jackEngine->availableActions($jack,'user:1');
check(count($actions)===4&&($actions[0]['steps']??null)===0,'Jackaroo Ace can release any home marble');
$payload=$actions[0];unset($payload['type'],$payload['label']);$jack=$jackEngine->apply($jack,'user:1','play_card',$payload);
check($jack['pieces']['user:1'][0]===0,'Jackaroo release action updates the marble');

echo "\nAll v142 dedicated rule-core checks passed.\n";
