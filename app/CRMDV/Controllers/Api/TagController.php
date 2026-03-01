<?php

namespace App\CRMDV\Controllers\Api;
use App\Http\Controllers\Admin\CURDBaseController;
use App\Http\Helpers\CommonHelper;
use Illuminate\Http\Request;
use App\CRMDV\Models\Department;
use App\CRMDV\Models\Tag;
use Validator;
use App\Models\Roles;
class TagController extends CURDBaseController
{

    protected $module = [
        'code' => 'tag',
        'table_name' => 'tags',
        'label' => 'Thẻ',
        'modal' => '\App\CRMDV\Models\Tag',
        'list' => [
            ['name' => 'name', 'type' => 'text_edit', 'label' => 'Tên'],
            ['name' => 'type', 'type' => 'text', 'label' => 'Loại',],
            ['name' => 'status', 'type' => 'status', 'label' => 'Trang thái'],
            ['name' => 'color', 'type' => 'color', 'label' => 'Màu hiển thị'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'name', 'type' => 'text', 'class' => '', 'label' => 'Tên', 'group_class' => 'col-md-6'],
                ['name' => 'type', 'type' => 'text', 'class' => '', 'label' => 'Loại', 'group_class' => 'col-md-6'],
                ['name' => 'status', 'type' => 'checkbox', 'label' => 'Kich hoạt', 'value' => 1, 'group_class' => 'col-md-4'],
                ['name' => 'color', 'type' => 'color', 'class' => '', 'label' => 'Màu nhận diện', 'group_class' => 'col-md-6'],
            ],
        ]
    ];

    protected $quick_search = [
        'label' => 'ID',
        'fields' => 'id, type'
    ];

    protected $filter = [
        'status' => [
            'label' => 'Trạng thái',
            'type' => 'select',
            'options' => [
                '' => 'Tất cả',
                0 => 'Không kich hoạt',
                1 => 'Kich hoạt',
            ],
            'query_type' => '='
        ],
    ];
    public function getDepartments()
    {
        $tags = Tag::where('type', 'phong_ban')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tags
        ]);
    }
    public function getAllRole()
    {
        $roles = Roles::with('admins')
            ->get();
        $roles1 = Roles::select('display_name')
            ->get();
        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name'=>$role->display_name,
                'admins' => $role->admins->pluck('name')->implode(', '),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
    public function getBanks()
{
    $names = Tag::where('type', 'bill_receipts')
        ->pluck('name'); // Chỉ lấy cột name, trả về Collection

    return response()->json([
        'status' => 'success',
        'msg'    => 'Lấy danh sách ngân hàng thành công',
        'data'   => $names
    ]);
}
    public function getRoom()
    {
        $names = Tag::where('type', 'phong_ban')
            ->pluck('name'); // Chỉ lấy cột name, trả về Collection

        return response()->json([
            'status' => 'success',
            'msg'    => 'Lấy danh sách ngân hàng thành công',
            'data'   => $names
        ]);
    }





}