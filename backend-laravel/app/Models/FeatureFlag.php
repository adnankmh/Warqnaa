<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['key','enabled','payload','environment'];
    protected $casts = ['enabled'=>'boolean','payload'=>'array'];
}
