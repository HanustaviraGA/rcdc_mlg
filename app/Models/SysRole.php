<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SysMenu;

class SysRole extends Model
{
    use HasFactory;
    protected $table = 'sys_role';
    protected $primaryKey = 'role_id';
    protected $keyType = 'string';
    protected $fillable = [
        'role_id',
        'role_name',
        'created_at',
        'updated_at'
    ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(SysMenu::class, 'sys_role_menu', 'role_menu_role_id', 'role_menu_menu_id');
    }
}
