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
            $sidebar = '<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu" data-kt-menu="true">';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();

            foreach ($start as $judul) {
                // $sidebar .= '
                // <div class="menu-item">
                //     <div class="menu-content pb-2">
                //         <span style="text-weight: bold !important; color: white !important;" class="menu-section text-muted text-uppercase fs-8 ls-1">'.$judul['menu_judul'].'</span>
                //         <span class="menu-arrow" style="color: white !important;">X</span>
                //     </div>
                // </div>';
                if (isset($judul['menu_link']) && $judul['menu_link'] !== '') {
                    $link = $judul['menu_link'];
                } else {
                    $link = 'javascript:void(0)';
                }

                if ($judul['menu_kode'] == 'home') {
                    $span = '';
                } else {
                    // $span = '<span class="menu-arrow"></span>';
                    $span = '<span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="rgba(152,153,172,1)"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span>';
                }

                $sidebar .= '
                <div class="menu-item menu-accordion">
                    <a href="javascript:void(0)" class="menu-link">
                        <span class="menu-title text-white fw-bold">' . $judul['menu_judul'] . '</span>
                    </a>
                </div>';
                if ($judul['menu_sub']) {
                    // Level 2 - Sub Menu Level 2
                    $start_c1 = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $judul['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach ($start_c1 as $values) {
                        if ($values['menu_sub'] == 1) {
                            $sidebar .= '
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">';

                            $extend = SysMenu::where('menu_aktif', 1)
                                ->where('menu_parent', $values['menu_id'])
                                ->where('menu_level', 3)
                                ->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })
                                ->orderBy('menu_order', 'asc')
                                ->get();

                            foreach ($extend as $valext) {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $values['menu_judul'] . '" data-page="' . $valext['menu_judul'] . '"
                                    id="lnk-' . $valext['menu_kode'] . '"
                                    class="menu-link" href="/' . $valext['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">' . $valext['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }

                            $sidebar .= '</div></div>'; // close submenu + parent
                        } else {
                            if ($values['menu_kode'] == 'dashboard') {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link active" href="/" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            } else {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link" href="/' . $values['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }
                        }
                    }
                }
            }

            $sidebar .= '</div>';

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
            $sidebar = '<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu" data-kt-menu="true">';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();

            foreach ($start as $judul) {
                // $sidebar .= '
                // <div class="menu-item">
                //     <div class="menu-content pb-2">
                //         <span style="text-weight: bold !important; color: white !important;" class="menu-section text-muted text-uppercase fs-8 ls-1">'.$judul['menu_judul'].'</span>
                //         <span class="menu-arrow" style="color: white !important;">X</span>
                //     </div>
                // </div>';
                if (isset($judul['menu_link']) && $judul['menu_link'] !== '') {
                    $link = $judul['menu_link'];
                } else {
                    $link = 'javascript:void(0)';
                }

                if ($judul['menu_kode'] == 'home') {
                    $span = '';
                } else {
                    // $span = '<span class="menu-arrow"></span>';
                    $span = '<span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="rgba(152,153,172,1)"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span>';
                }

                $sidebar .= '
                <div class="menu-item menu-accordion">
                    <a href="javascript:void(0)" class="menu-link">
                        <span class="menu-title text-white fw-bold">' . $judul['menu_judul'] . '</span>
                    </a>
                </div>';
                if ($judul['menu_sub']) {
                    // Level 2 - Sub Menu Level 2
                    $start_c1 = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $judul['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach ($start_c1 as $values) {
                        if ($values['menu_sub'] == 1) {
                            $sidebar .= '
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">';

                            $extend = SysMenu::where('menu_aktif', 1)
                                ->where('menu_parent', $values['menu_id'])
                                ->where('menu_level', 3)
                                ->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })
                                ->orderBy('menu_order', 'asc')
                                ->get();

                            foreach ($extend as $valext) {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $values['menu_judul'] . '" data-page="' . $valext['menu_judul'] . '"
                                    id="lnk-' . $valext['menu_kode'] . '"
                                    class="menu-link" href="/' . $valext['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">' . $valext['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }

                            $sidebar .= '</div></div>'; // close submenu + parent
                        } else {
                            if ($values['menu_kode'] == 'dashboard') {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link active" href="/" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            } else {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link" href="/' . $values['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }
                        }
                    }
                }
            }

            $sidebar .= '</div>';

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
            $sidebar = '<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu" data-kt-menu="true">';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();

            foreach ($start as $judul) {
                // $sidebar .= '
                // <div class="menu-item">
                //     <div class="menu-content pb-2">
                //         <span style="text-weight: bold !important; color: white !important;" class="menu-section text-muted text-uppercase fs-8 ls-1">'.$judul['menu_judul'].'</span>
                //         <span class="menu-arrow" style="color: white !important;">X</span>
                //     </div>
                // </div>';
                if (isset($judul['menu_link']) && $judul['menu_link'] !== '') {
                    $link = $judul['menu_link'];
                } else {
                    $link = 'javascript:void(0)';
                }

                if ($judul['menu_kode'] == 'home') {
                    $span = '';
                } else {
                    // $span = '<span class="menu-arrow"></span>';
                    $span = '<span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="rgba(152,153,172,1)"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span>';
                }

                $sidebar .= '
                <div class="menu-item menu-accordion">
                    <a href="javascript:void(0)" class="menu-link">
                        <span class="menu-title text-white fw-bold">' . $judul['menu_judul'] . '</span>
                    </a>
                </div>';
                if ($judul['menu_sub']) {
                    // Level 2 - Sub Menu Level 2
                    $start_c1 = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $judul['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach ($start_c1 as $values) {
                        if ($values['menu_sub'] == 1) {
                            $sidebar .= '
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">';

                            $extend = SysMenu::where('menu_aktif', 1)
                                ->where('menu_parent', $values['menu_id'])
                                ->where('menu_level', 3)
                                ->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })
                                ->orderBy('menu_order', 'asc')
                                ->get();

                            foreach ($extend as $valext) {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $values['menu_judul'] . '" data-page="' . $valext['menu_judul'] . '"
                                    id="lnk-' . $valext['menu_kode'] . '"
                                    class="menu-link" href="/' . $valext['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">' . $valext['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }

                            $sidebar .= '</div></div>'; // close submenu + parent
                        } else {
                            if ($values['menu_kode'] == 'dashboard') {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link active" href="/" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            } else {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link" href="/' . $values['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }
                        }
                    }
                }
            }

            $sidebar .= '</div>';

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
            $sidebar = '<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu" data-kt-menu="true">';
            // Level 1 - Judul Menu
            $start = SysMenu::where('menu_aktif', 1)->where('menu_level', 1)->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role);
            })->orderBy('menu_order', 'asc')->get();

            foreach ($start as $judul) {
                // $sidebar .= '
                // <div class="menu-item">
                //     <div class="menu-content pb-2">
                //         <span style="text-weight: bold !important; color: white !important;" class="menu-section text-muted text-uppercase fs-8 ls-1">'.$judul['menu_judul'].'</span>
                //         <span class="menu-arrow" style="color: white !important;">X</span>
                //     </div>
                // </div>';

                if ($judul['menu_kode'] == 'home') {
                    $span = '';
                } else {
                    // $span = '<span class="menu-arrow"></span>';
                    $span = '<span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="rgba(152,153,172,1)"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span>';
                }

                $sidebar .= '
                <div class="menu-item menu-accordion">
                    <a href="javascript:void(0)" class="menu-link">
                        <span class="menu-title text-white fw-bold">' . $judul['menu_judul'] . '</span>
                    </a>
                </div>';
                if ($judul['menu_sub']) {
                    // Level 2 - Sub Menu Level 2
                    $start_c1 = SysMenu::where('menu_aktif', 1)->where('menu_level', 2)->where('menu_parent', $judul['menu_id'])->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role);
                    })->orderBy('menu_order', 'asc')->get();
                    foreach ($start_c1 as $values) {
                        if ($values['menu_sub'] == 1) {
                            $sidebar .= '
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <span class="menu-link">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <div class="menu-sub menu-sub-accordion">';

                            $extend = SysMenu::where('menu_aktif', 1)
                                ->where('menu_parent', $values['menu_id'])
                                ->where('menu_level', 3)
                                ->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role);
                                })
                                ->orderBy('menu_order', 'asc')
                                ->get();

                            foreach ($extend as $valext) {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $values['menu_judul'] . '" data-page="' . $valext['menu_judul'] . '"
                                    id="lnk-' . $valext['menu_kode'] . '"
                                    class="menu-link" href="/' . $valext['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">' . $valext['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }

                            $sidebar .= '</div></div>'; // close submenu + parent
                        } else {
                            if ($values['menu_kode'] == 'dashboard') {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link active" href="/" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            } else {
                                $sidebar .= '
                                <div class="menu-item">
                                    <a data-page-parent="' . $judul['menu_judul'] . '" data-page="' . $values['menu_judul'] . '" id="lnk-' . $values['menu_kode'] . '" class="menu-link" href="/' . $values['menu_kode'] . '" data-navigo>
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">' . $values['menu_judul'] . '</span>
                                    </a>
                                </div>';
                            }
                        }
                    }
                }
            }

            $sidebar .= '</div>';

            return view('dashboard.index', compact('destination', 'ucf', 'roles', 'sidebar'));
        }
    }

    public function change_perms(){
        $user = User::find(1);
        Auth::login($user);
        return redirect()->route('index');
    }
}
