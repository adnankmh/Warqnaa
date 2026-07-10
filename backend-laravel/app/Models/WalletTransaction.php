<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class WalletTransaction extends Model { protected $fillable=['user_id','counterparty_id','type','amount','fee','meta']; protected $casts=['meta'=>'array']; }
