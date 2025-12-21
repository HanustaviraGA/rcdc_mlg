<?php

namespace Modules\HakAkses\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\SysRoleMenu;
use App\Models\SysRole;
use Illuminate\Support\Facades\Auth;

class HakAksesController extends Controller
{
    /**
     * Display a listing of the resource with loadPage helper.
     * @return Renderable
     */
    public function index()
    {
        return loadPage('hakakses::index');
    }

    public function getMenuList(Request $request){
        $data = $request->all();
        $roleId = $data['roleId'];
        $query = new SysRoleMenu;
        $operation = $query->select_menu($roleId);
        return response()->json($operation);
    }

    public function getRoleList(){
        $query = SysRole::orderBy('role_name', 'asc')->get();
        $html = '';
        $i = 1;
        foreach($query as $values){
            $html .= '
            <tr>
                <td class="text-center text-nowrap">'.($i).'</td>
                <td title="Klik untuk edit" data-role_id="'.$values['role_id'].'" onclick="editRow(this)" style="white-space:nowrap;cursor:pointer" data-value="'.$values['role_name'].'">'.$values['role_name'].'</td>
                <td style="white-space:nowrap; width: 40px;">
                    <a href="javascript:;" data-role_id="'.$values['role_id'].'" onclick="deleteHA(this)" data-roleable="true" data-role="HakAccess-Delete" class="btn btn-sm btn-clean btn-icon" title="Delete"> <i class="far fa-trash-alt fa-lg text-danger"></i></a>
                    <a href="javascript:;" data-role_id="'.$values['role_id'].'" onclick="loadHakAkses(this)" data-roleable="true" data-role="HakAccess-Detail" class="btn btn-sm btn-clean btn-icon" title="Detail"> <i class="fa fa-arrow-right text-success"></i></a>
                </td>
            </tr>';
            $i++;
        }
        $arr =[
            'roles' => base64_encode($html)
        ];
        return $arr;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request){
        $data = $request->all();
        $roles = $data['roles'];
        $roleId = $data['role_id'];
        $operation = SysRoleMenu::where('role_menu_role_id', $roleId)->delete();
        $newrole = [];
        foreach ($roles as $key => $value) {
            array_push($newrole, [
                'role_menu_id' => md5(rand(0, 100).generateCode()),
                'role_menu_role_id' => $roleId,
                'role_menu_menu_id' => $value
            ]);
        }
        \DB::beginTransaction();
        try{
            $query = SysRoleMenu::insert($newrole);
            \DB::commit();
            if((isset(Auth::user()->role_id) && $roleId == Auth::user()->role_id) || env('APP_ENV') == 'local'){
                $reload = true;
            }else if((isset(Auth::user()->role_id) && $roleId != Auth::user()->role_id) || env('APP_ENV') != 'local'){
                $reload = false;
            }
            return response()->json([
                'success' => $query,
                'reload' => $reload,
                'roles' => count($roles)
            ], 200);
        }catch(\Exception $e){
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function read(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $data = $request->all();
    }
}
