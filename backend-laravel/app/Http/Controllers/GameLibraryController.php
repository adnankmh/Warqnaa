<?php
namespace App\Http\Controllers;

class GameLibraryController
{
 public function index()
 {
  $matrix=config('warqna_games_matrix');
  return view('games.library_pro',['matrix'=>$matrix]);
 }
}
