<?php

namespace App\CRMDV\Controllers\Api;
use App\Http\Controllers\Admin\CURDBaseController;
use App\Http\Helpers\CommonHelper;
use App\Models\Admin;
use App\Models\PermissionRole;
use App\Models\RoleAdmin;
use App\Models\Roles;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class RoleController extends CURDBaseController
{
    protected $view = 'role';

    protected $module = [
        'code' => 'role',
        'label' => 'admin.role',
        'table_name' => 'roles',
        'modal' => '\App\Models\Roles',
        'list' => [
            ['name' => 'display_name', 'type' => 'text_edit', 'label' => 'admin.permission'],
            ['name' => 'description', 'type' => 'text', 'label' => 'admin.describe'],
            ['name' => 'total', 'type' => 'text', 'label' => 'Số lượng'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'display_name', 'type' => 'text', 'class' => 'require', 'label' => 'admin.name'],
                ['name' => 'description', 'type' => 'textarea', 'label' => 'admin.describe'],
            ],
        ],
    ];

    protected $filter = [
        'display_name' => [
            'label' => 'admin.permission',
            'type' => 'text',
            'query_type' => 'like'
        ],
    ];
    public function index(Request $request)
    {
        $query = Roles::query();
        if ($request->display_name) {
            $query->where('display_name', 'like', '%' . $request->display_name . '%');
        }
        $roles = $query->paginate(20);
        $data = $roles->getCollection()->map(function ($item) {
            return $item->only(['id', 'display_name', 'description','name']);
        });
        return response()->json(
            [
                'status'=>true,
                'msg'=>'Lấy dữ liệu thành công',
                'data' => $data]
        );
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Roles::create($request->all());

        return response()->json($role, 201);
    }
    public function show($id)
    {
        $role = Roles::findOrFail($id);
        return response()->json([
            'status'=>true,
            'msg'=>'Lấy dữ liệu thành công',
            'data' => $role->only(['id', 'display_name', 'description','name'])
        ]);
    }
    public function update(Request $request, $id)
    {
        $role = Roles::findOrFail($id);

        $role->update($request->all());

        return response()->json($role);
    }
    public function destroy($id)
    {
        $role = Roles::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

}
