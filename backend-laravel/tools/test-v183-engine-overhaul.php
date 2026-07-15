<?php
/** Standalone V183 engine regression checks; no Laravel bootstrap required. */
$base=__DIR__.'/../app/Services/GameEngine/GlobalEngines';
require_once "$base/Tarneeb400Engine.php";
require_once "$base/SaudiHandEngine.php";
require_once "$base/HandPartnershipEngine.php";
require_once "$base/BanakilEngine.php";
require_once "$base/TrixEngine.php";
require_once "$base/TrixComplexEngine.php";
require_once "$base/BalootEngine.php";
function v183Players(int $n): array { $r=[]; for($i=0;$i<$n;$i++)$r[]=['id'=>'p'.$i,'name'=>'P'.$i,'bot'=>false]; return $r; }
function v183Assert(bool $condition,string $message): void { if(!$condition){fwrite(STDERR,"[FAIL] $message\n");exit(1);} echo "[PASS] $message\n"; }
function firstAction(array $actions,string $type): array { foreach($actions as $action)if(($action['type']??'')===$type)return $action; throw new RuntimeException("Missing action $type"); }

$engine=new Tarneeb400Engine(); $state=$engine->newGame(v183Players(4),['seed'=>183]);
v183Assert(count($state['hands']['p0'])===13,'Tarneeb 400 deals 13 cards');
$declarations=[3,3,3,2]; for($i=0;$i<4;$i++){ $pid=$state['players'][$state['currentIndex']]['id']; $state=$engine->applyAction($state,$pid,['type'=>'bid','amount'=>$declarations[$i]]); }
v183Assert($state['phase']==='playing' && $state['trump']==='H','Tarneeb 400 independent declarations start fixed-hearts play');
v183Assert(count($state['scores'])===4,'Tarneeb 400 keeps individual scores inside teams');

$hand=new SaudiHandEngine(); $state=$hand->newGame(v183Players(5),['seed'=>183]);
v183Assert(count($state['hands']['p0'])===15 && count($state['hands']['p4'])===14,'Saudi Hand supports 2-5 and deals 15/14');
$actions=$hand->availableActions($state,'p0');
v183Assert(!array_filter($actions,fn($a)=>in_array(($a['type']??''),['meld','meld_many','layoff'],true)),'Hand starter must discard extra card before melding');
$state=$hand->applyAction($state,'p0',firstAction($actions,'discard'));
v183Assert($state['phase']==='draw','Hand enters draw cycle after starter discard');

$banakil=new BanakilEngine(); $state=$banakil->newGame(v183Players(4),['seed'=>183]);
v183Assert(count($state['hands']['p0'])===19 && count($state['hands']['p1'])===18,'Banakil deals 19/18');

$trix=new TrixEngine(); $state=$trix->newGame(v183Players(4),['seed'=>183]);
$state=$trix->applyAction($state,'p0',['type'=>'choose_contract','contract'=>'trix']);
v183Assert($state['phase']==='trix_playing','Trix uses board-building phase');
$guard=0; while(!$state['gameOver'] && $state['round']===1 && $guard++<300){ $pid=$state['players'][$state['currentIndex']]['id']; $actions=$trix->availableActions($state,$pid); $state=$trix->applyAction($state,$pid,$actions[0]); }
v183Assert($state['round']===2 || $state['gameOver'],'Trix completes and advances a contract');

$baloot=new BalootEngine(); $state=$baloot->newGame(v183Players(4),['seed'=>183]); $actions=$baloot->availableActions($state,'p0');
v183Assert((bool)array_filter($actions,fn($a)=>($a['contract']??'')==='sun'),'Baloot offers Sun');
v183Assert((bool)array_filter($actions,fn($a)=>($a['contract']??'')==='hokm'),'Baloot offers Hokm');
echo "[PASS] V183 engine overhaul contract\n";
