<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppRelease extends Model
{
    protected $fillable = ['platform','version','build_number','required','active','notes','download_url'];
    protected $casts = ['required'=>'boolean','active'=>'boolean'];
}
