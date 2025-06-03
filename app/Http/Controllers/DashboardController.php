<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SysMenu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Session;

class DashboardController extends Controller
{
    public function index(Request $request){
        $auth = Auth::check();
        // $auth = true;
        if ($auth) {
            $destination = 'home';
            $ucf = ucfirst($destination);

            // Role
            // $role = Auth::user()->role_id;
            $role = 'OWN001';
            $isi_roles = [];
            
            // $query = DB::table('view_sys_role_menu')
            // ->where('role_id', $role)
            // ->where('menu_aktif', '1')
            // ->select('menu_kode')
            // ->get();

            $query = DB::table('sys_role_menu as crm')
            ->leftJoin('sys_menu as cm', 'crm.role_menu_menu_id', '=', 'cm.menu_id')
            ->leftJoin('sys_role as cr', 'crm.role_menu_role_id', '=', 'cr.role_id')
            ->where('cr.role_id', $role)
            ->where('cm.menu_aktif', '1')
            ->select(
                'crm.role_menu_id',
                'crm.role_menu_menu_id',
                'crm.role_menu_role_id',
                'cm.menu_id',
                'cm.menu_kode',
                'cm.menu_judul',
                'cm.menu_order',
                'cm.menu_parent',
                'cm.menu_aktif',
                'cm.menu_icon',
                'cm.menu_level',
                'cm.menu_sub',
                'cm.created_at',
                'cm.updated_at',
                'cr.role_id',
                'cr.role_name'
            )
            ->get();


            foreach($query as $values){
                $isi_roles[] = $values->menu_kode;
            }
            $roles = json_encode($isi_roles);

            // Sidebar
            $sidebar = '';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();
            foreach($start as $level_one){
                $sidebar .= '<div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">'.$level_one['menu_judul'].'</span>
                    </div>
                </div>';
                if($level_one['menu_sub']){
                    // Level 2 - Sub Menu Level 2
                    $second = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $level_one['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach($second as $level_two){
                        // Nested
                        if($level_two['menu_sub']){
                            $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                // Level 3 - Sub Menu Level 3
                                $third = SysMenu::where('menu_aktif', 1)->where('menu_level', 3)->where('menu_parent', $level_two['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })->orderBy('menu_order', 'asc')->get();
                                foreach($third as $level_three){
                                    if($level_three['menu_sub']){
                                        $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                            <span class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                                <span class="menu-arrow"></span>
                                            </span>
                                            <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                                // Level 4 - Sub Menu Level 4
                                                $fourth = SysMenu::where('menu_aktif', 1)->where('menu_level', 4)->where('menu_parent', $level_three['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                                    $query->where('role_id', $role);
                                                })->orderBy('menu_order', 'asc')->get();
                                                foreach($fourth as $level_four){
                                                    $sidebar .= '<div class="menu-item">
                                                        <a data-page="' . $level_four['menu_judul'] . '" id="lnk-'.$level_four['menu_kode'].'" class="menu-link" href="/'.$level_four['menu_kode'].'" data-navigo>
                                                            <span class="menu-bullet">
                                                                <span class="bullet bullet-dot"></span>
                                                            </span>
                                                            <span class="menu-title">'.$level_four['menu_judul'].'</span>
                                                        </a>
                                                    </div>';
                                                }
                                        $sidebar .= '</div></div>';
                                    }else{
                                        $sidebar .= '<div class="menu-item">
                                            <a data-page="' . $level_three['menu_judul'] . '" id="lnk-'.$level_three['menu_kode'].'" class="menu-link" href="/'.$level_three['menu_kode'].'" data-navigo>
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                            </a>
                                        </div>';
                                    }
                                }
                            $sidebar .= '</div></div>';
                        }
                        // Non - nested
                        else{
                            $sidebar .= '<div class="menu-item">
                                <a data-page="' . $level_two['menu_judul'] . '" id="lnk-'.$level_two['menu_kode'].'" class="menu-link" href="/'.$level_two['menu_kode'].'" data-navigo>
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                </a>
                            </div>';
                        }
                    }
                }
            }
            return view('dashboard.index', compact('destination', 'ucf', 'roles', 'sidebar'));
        }else{
            $destination = 'home';
            $ucf = ucfirst($destination);

            // Role
            // $role = Auth::user()->role_id;
            $role = 'DSN001';
            $isi_roles = [];
            
            // $query = DB::table('view_sys_role_menu')
            // ->where('role_id', $role)
            // ->where('menu_aktif', '1')
            // ->select('menu_kode')
            // ->get();

            $query = DB::table('sys_role_menu as crm')
            ->leftJoin('sys_menu as cm', 'crm.role_menu_menu_id', '=', 'cm.menu_id')
            ->leftJoin('sys_role as cr', 'crm.role_menu_role_id', '=', 'cr.role_id')
            ->where('cr.role_id', $role)
            ->where('cm.menu_aktif', '1')
            ->select(
                'crm.role_menu_id',
                'crm.role_menu_menu_id',
                'crm.role_menu_role_id',
                'cm.menu_id',
                'cm.menu_kode',
                'cm.menu_judul',
                'cm.menu_order',
                'cm.menu_parent',
                'cm.menu_aktif',
                'cm.menu_icon',
                'cm.menu_level',
                'cm.menu_sub',
                'cm.created_at',
                'cm.updated_at',
                'cr.role_id',
                'cr.role_name'
            )
            ->get();


            foreach($query as $values){
                $isi_roles[] = $values->menu_kode;
            }
            $roles = json_encode($isi_roles);

            // Sidebar
            $sidebar = '';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();
            foreach($start as $level_one){
                $sidebar .= '<div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">'.$level_one['menu_judul'].'</span>
                    </div>
                </div>';
                if($level_one['menu_sub']){
                    // Level 2 - Sub Menu Level 2
                    $second = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $level_one['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach($second as $level_two){
                        // Nested
                        if($level_two['menu_sub']){
                            $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                // Level 3 - Sub Menu Level 3
                                $third = SysMenu::where('menu_aktif', 1)->where('menu_level', 3)->where('menu_parent', $level_two['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })->orderBy('menu_order', 'asc')->get();
                                foreach($third as $level_three){
                                    if($level_three['menu_sub']){
                                        $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                            <span class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                                <span class="menu-arrow"></span>
                                            </span>
                                            <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                                // Level 4 - Sub Menu Level 4
                                                $fourth = SysMenu::where('menu_aktif', 1)->where('menu_level', 4)->where('menu_parent', $level_three['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                                    $query->where('role_id', $role);
                                                })->orderBy('menu_order', 'asc')->get();
                                                foreach($fourth as $level_four){
                                                    $sidebar .= '<div class="menu-item">
                                                        <a data-page="' . $level_four['menu_judul'] . '" id="lnk-'.$level_four['menu_kode'].'" class="menu-link" href="/'.$level_four['menu_kode'].'" data-navigo>
                                                            <span class="menu-bullet">
                                                                <span class="bullet bullet-dot"></span>
                                                            </span>
                                                            <span class="menu-title">'.$level_four['menu_judul'].'</span>
                                                        </a>
                                                    </div>';
                                                }
                                        $sidebar .= '</div></div>';
                                    }else{
                                        $sidebar .= '<div class="menu-item">
                                            <a data-page="' . $level_three['menu_judul'] . '" id="lnk-'.$level_three['menu_kode'].'" class="menu-link" href="/'.$level_three['menu_kode'].'" data-navigo>
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                            </a>
                                        </div>';
                                    }
                                }
                            $sidebar .= '</div></div>';
                        }
                        // Non - nested
                        else{
                            $sidebar .= '<div class="menu-item">
                                <a data-page="' . $level_two['menu_judul'] . '" id="lnk-'.$level_two['menu_kode'].'" class="menu-link" href="/'.$level_two['menu_kode'].'" data-navigo>
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                </a>
                            </div>';
                        }
                    }
                }
            }
            return view('dashboard.index', compact('destination', 'ucf', 'roles', 'sidebar'));
        }
    }

    public function index_spec($any){
        $auth = Auth::check();
        // $auth = true;
        if ($auth) {
            $destination = $any;
            $ucf = ucfirst($destination);

            // Role
            // $role = Auth::user()->role_id;
            $role = 'OWN001';
            $isi_roles = [];

            // $query = DB::table('view_sys_role_menu')
            // ->where('role_id', $role)
            // ->where('menu_aktif', '1')
            // ->select('menu_kode')
            // ->get();

            $query = DB::table('sys_role_menu as crm')
            ->leftJoin('sys_menu as cm', 'crm.role_menu_menu_id', '=', 'cm.menu_id')
            ->leftJoin('sys_role as cr', 'crm.role_menu_role_id', '=', 'cr.role_id')
            ->where('cr.role_id', $role)
            ->where('cm.menu_aktif', '1')
            ->select(
                'crm.role_menu_id',
                'crm.role_menu_menu_id',
                'crm.role_menu_role_id',
                'cm.menu_id',
                'cm.menu_kode',
                'cm.menu_judul',
                'cm.menu_order',
                'cm.menu_parent',
                'cm.menu_aktif',
                'cm.menu_icon',
                'cm.menu_level',
                'cm.menu_sub',
                'cm.created_at',
                'cm.updated_at',
                'cr.role_id',
                'cr.role_name'
            )
            ->get();

            foreach($query as $values){
                $isi_roles[] = $values->menu_kode;
            }
            $roles = json_encode($isi_roles);

            // Sidebar
            $sidebar = '';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();
            foreach($start as $level_one){
                $sidebar .= '<div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">'.$level_one['menu_judul'].'</span>
                    </div>
                </div>';
                if($level_one['menu_sub']){
                    // Level 2 - Sub Menu Level 2
                    $second = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $level_one['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach($second as $level_two){
                        // Nested
                        if($level_two['menu_sub']){
                            $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                // Level 3 - Sub Menu Level 3
                                $third = SysMenu::where('menu_aktif', 1)->where('menu_level', 3)->where('menu_parent', $level_two['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })->orderBy('menu_order', 'asc')->get();
                                foreach($third as $level_three){
                                    if($level_three['menu_sub']){
                                        $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                            <span class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                                <span class="menu-arrow"></span>
                                            </span>
                                            <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                                // Level 4 - Sub Menu Level 4
                                                $fourth = SysMenu::where('menu_aktif', 1)->where('menu_level', 4)->where('menu_parent', $level_three['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                                    $query->where('role_id', $role);
                                                })->orderBy('menu_order', 'asc')->get();
                                                foreach($fourth as $level_four){
                                                    $sidebar .= '<div class="menu-item">
                                                        <a data-page="' . $level_four['menu_judul'] . '" id="lnk-'.$level_four['menu_kode'].'" class="menu-link" href="/'.$level_four['menu_kode'].'" data-navigo>
                                                            <span class="menu-bullet">
                                                                <span class="bullet bullet-dot"></span>
                                                            </span>
                                                            <span class="menu-title">'.$level_four['menu_judul'].'</span>
                                                        </a>
                                                    </div>';
                                                }
                                        $sidebar .= '</div></div>';
                                    }else{
                                        $sidebar .= '<div class="menu-item">
                                            <a data-page="' . $level_three['menu_judul'] . '" id="lnk-'.$level_three['menu_kode'].'" class="menu-link" href="/'.$level_three['menu_kode'].'" data-navigo>
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                            </a>
                                        </div>';
                                    }
                                }
                            $sidebar .= '</div></div>';
                        }
                        // Non - nested
                        else{
                            $sidebar .= '<div class="menu-item">
                                <a data-page="' . $level_two['menu_judul'] . '" id="lnk-'.$level_two['menu_kode'].'" class="menu-link" href="/'.$level_two['menu_kode'].'" data-navigo>
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                </a>
                            </div>';
                        }
                    }
                }
            }
            return view('dashboard.index', compact('destination', 'ucf', 'roles', 'sidebar'));
        }else{
            $destination = $any;
            $ucf = ucfirst($destination);

            // Role
            // $role = Auth::user()->role_id;
            $role = 'DSN001';
            $isi_roles = [];
            
            // $query = DB::table('view_sys_role_menu')
            // ->where('role_id', $role)
            // ->where('menu_aktif', '1')
            // ->select('menu_kode')
            // ->get();

            $query = DB::table('sys_role_menu as crm')
            ->leftJoin('sys_menu as cm', 'crm.role_menu_menu_id', '=', 'cm.menu_id')
            ->leftJoin('sys_role as cr', 'crm.role_menu_role_id', '=', 'cr.role_id')
            ->where('cr.role_id', $role)
            ->where('cm.menu_aktif', '1')
            ->select(
                'crm.role_menu_id',
                'crm.role_menu_menu_id',
                'crm.role_menu_role_id',
                'cm.menu_id',
                'cm.menu_kode',
                'cm.menu_judul',
                'cm.menu_order',
                'cm.menu_parent',
                'cm.menu_aktif',
                'cm.menu_icon',
                'cm.menu_level',
                'cm.menu_sub',
                'cm.created_at',
                'cm.updated_at',
                'cr.role_id',
                'cr.role_name'
            )
            ->get();


            foreach($query as $values){
                $isi_roles[] = $values->menu_kode;
            }
            $roles = json_encode($isi_roles);

            // Sidebar
            $sidebar = '';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();
            foreach($start as $level_one){
                $sidebar .= '<div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">'.$level_one['menu_judul'].'</span>
                    </div>
                </div>';
                if($level_one['menu_sub']){
                    // Level 2 - Sub Menu Level 2
                    $second = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $level_one['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach($second as $level_two){
                        // Nested
                        if($level_two['menu_sub']){
                            $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                // Level 3 - Sub Menu Level 3
                                $third = SysMenu::where('menu_aktif', 1)->where('menu_level', 3)->where('menu_parent', $level_two['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })->orderBy('menu_order', 'asc')->get();
                                foreach($third as $level_three){
                                    if($level_three['menu_sub']){
                                        $sidebar .= '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                            <span class="menu-link">
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                                <span class="menu-arrow"></span>
                                            </span>
                                            <div class="menu-sub menu-sub-accordion menu-active-bg">';
                                                // Level 4 - Sub Menu Level 4
                                                $fourth = SysMenu::where('menu_aktif', 1)->where('menu_level', 4)->where('menu_parent', $level_three['menu_id'])->whereHas('roles', function ($query) use ($role) {
                                                    $query->where('role_id', $role);
                                                })->orderBy('menu_order', 'asc')->get();
                                                foreach($fourth as $level_four){
                                                    $sidebar .= '<div class="menu-item">
                                                        <a data-page="' . $level_four['menu_judul'] . '" id="lnk-'.$level_four['menu_kode'].'" class="menu-link" href="/'.$level_four['menu_kode'].'" data-navigo>
                                                            <span class="menu-bullet">
                                                                <span class="bullet bullet-dot"></span>
                                                            </span>
                                                            <span class="menu-title">'.$level_four['menu_judul'].'</span>
                                                        </a>
                                                    </div>';
                                                }
                                        $sidebar .= '</div></div>';
                                    }else{
                                        $sidebar .= '<div class="menu-item">
                                            <a data-page="' . $level_three['menu_judul'] . '" id="lnk-'.$level_three['menu_kode'].'" class="menu-link" href="/'.$level_three['menu_kode'].'" data-navigo>
                                                <span class="menu-bullet">
                                                    <span class="bullet bullet-dot"></span>
                                                </span>
                                                <span class="menu-title">'.$level_three['menu_judul'].'</span>
                                            </a>
                                        </div>';
                                    }
                                }
                            $sidebar .= '</div></div>';
                        }
                        // Non - nested
                        else{
                            $sidebar .= '<div class="menu-item">
                                <a data-page="' . $level_two['menu_judul'] . '" id="lnk-'.$level_two['menu_kode'].'" class="menu-link" href="/'.$level_two['menu_kode'].'" data-navigo>
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">'.$level_two['menu_judul'].'</span>
                                </a>
                            </div>';
                        }
                    }
                }
            }
            return view('dashboard.index', compact('destination', 'ucf', 'roles', 'sidebar'));
        }
    }

    public function change_perms(){
        $user = User::find(1);
        Auth::login($user);
        return redirect()->route('index');
    }
}
