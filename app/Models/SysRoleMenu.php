<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SysRoleMenu extends Model
{
    use HasFactory;
    protected $table = 'sys_role_menu';
    protected $primaryKey = 'role_menu_id';
    protected $keyType = 'string';
    protected $fillable = [
        'role_menu_id',
        'role_menu_menu_id',
        'role_menu_role_id',
        'created_at',
        'updated_at'
    ];

    public function select_menu($roleId){
        $menus = DB::select("
            SELECT sys_menu.*,
            (SELECT COUNT(*) FROM sys_menu a WHERE menu_parent = sys_menu.menu_id) AS child,
            (SELECT COUNT(*) FROM sys_role_menu WHERE role_menu_menu_id = sys_menu.menu_id AND role_menu_role_id = '{$roleId}') AS selected
            FROM sys_menu
            ORDER BY menu_order ASC
        ");

        $menuList = [];
        $setted = [];

        foreach ($menus as $menu) {
            if ($menu->menu_parent == null) {
                $parent = '#';
            } else {
                $parent = $menu->menu_parent;
            }

            $state = ($menu->child == 0 && $menu->selected == '1') ? true : false;

            $menuList[] = [
                'id' => $menu->menu_id,
                'parent' => $parent,
                'text' => $menu->menu_judul,
                'icon' => $menu->menu_icon,
                'state' => [
                    'selected' => $state,
                    'opened' => false
                ]
            ];
        }

        return [
            'menu' => $menuList,
            'setted' => $setted
        ];
    }

}
