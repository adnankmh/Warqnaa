<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminDesignerEntity extends Model {
    protected $fillable=['entity_type','key','locale','payload','sort_order','active','revision','updated_by'];
    protected $casts=['payload'=>'array','active'=>'boolean','revision'=>'integer'];
}
