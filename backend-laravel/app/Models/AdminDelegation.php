<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDelegation extends Model
{
    protected $fillable = ['user_id','granted_by','permissions','active'];
    protected $casts = ['permissions'=>'array','active'=>'boolean'];

    public function user(){ return $this->belongsTo(User::class); }
    public function grantor(){ return $this->belongsTo(User::class, 'granted_by'); }

    public function allows(string $permission): bool
    {
        if (!$this->active) return false;
        $permissions = $this->permissions ?: [];
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
