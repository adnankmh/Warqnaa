<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable=['key','value','type','group','label'];
    public $timestamps=true;

    public static function getValue(string $key, mixed $default=null): mixed
    {
        $row=static::where('key',$key)->first();
        if(!$row) return $default;
        return match($row->type){
            'bool' => filter_var($row->value,FILTER_VALIDATE_BOOLEAN),
            'int' => (int)$row->value,
            'float' => (float)$row->value,
            'json' => json_decode((string)$row->value,true) ?: $default,
            default => $row->value,
        };
    }
    public static function setValue(string $key, mixed $value, string $type='string', string $group='general', string $label=''): void
    {
        $stored=$type==='json' ? json_encode($value,JSON_UNESCAPED_UNICODE) : (($type==='bool') ? ($value?'1':'0') : (string)$value);
        static::updateOrCreate(['key'=>$key],['value'=>$stored,'type'=>$type,'group'=>$group,'label'=>$label ?: $key]);
    }
}
