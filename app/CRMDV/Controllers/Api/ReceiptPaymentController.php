<?php

namespace App\CRMDV\Controllers\Api;
use App\CRMDV\Models\Admin;
use App\CRMDV\Models\Lead;
use Carbon\Carbon;
use App\CRMDV\Models\Bill;
use App\CRMDV\Models\BillReceipts;
use App\CRMDV\Models\Tag;
use App\Http\Helpers\CommonHelper;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use App\CRMDV\Controllers\Admin\CURDBaseController;
use Validator;
use Illuminate\Support\Facades\DB;

class ReceiptPaymentController extends CURDBaseController
{
    protected $orderByRaw = 'status ASC, date desc';

    protected $module = [
        'code' => 'receipt_payment',
        'table_name' => 'bill_receipts',
        'label' => 'Thu - chi',
        'modal' => '\App\CRMDV\Models\BillReceipts',
        'list' => [
            ['name' => 'date', 'type' => 'date_vi', 'label' => 'Ngày'],
            ['name' => 'price', 'type' => 'price_vi', 'label' => 'Số tiền'],
            ['name' => 'image', 'type' => 'image', 'label' => 'Ảnh CK'],
            ['name' => 'status', 'type' => 'status', 'label' => 'Duyệt phiếu'],
        ],
        'form' => [
            'general_tab' => [
                ['name' => 'date', 'type' => 'date', 'class' => 'required', 'label' => 'Ngày', 'value' => '', 'group_class' => 'col-md-3'],
                ['name' => 'receiving_account', 'type' => 'select2_model', 'label' => 'Tk nhận', 'model' => Tag::class,
                    'where' => 'type="bill_receipts"', 'object' => 'tag', 'display_field' => 'name', 'group_class' => 'col-md-3'],
                ['name' => 'bill_id', 'type' => 'text', 'class' => '', 'label' => 'ID hợp đồng', 'value' => '', 'group_class' => 'col-md-3'],
                ['name' => 'type', 'type' => 'select', 'options' => [
                    '' => '',
                    'luong' => 'Lương',
                    'dt' => 'Đầu tư',
                    'co_so_so' => 'Cơ sở số',
                    'luong_kd' => 'Lương KD',
                    'luong_kt' => 'Lương KT',
                    'phuc_loi' => 'Phúc lợi',
                    'co_so' => 'Cơ sở vật chất',
                    'khac' => 'Khác',
                ], 'class' => '', 'label' => 'Loại', 'group_class' => 'col-md-3'],
                ['name' => 'so_hoa_don', 'type' => 'number', 'class' => '', 'label' => 'Số hoá đơn', 'group_class' => 'col-sm-4'],
                ['name' => 'price', 'type' => 'price_vi', 'class' => 'required','label' => 'Số tiền giao dịch', 'group_class' => 'col-md-4'],
                ['name' => 'image', 'type' => 'file_image', 'class' => '', 'label' => 'Ảnh bằng chứng', 'group_class' => 'col-md-4'],
                ['name' => 'employees', 'type' => 'text', 'class' => '','label' => 'Tên người thực hiện', 'group_class' => 'col-md-4'],
                ['name' => 'note', 'type' => 'textarea', 'class' => 'required', 'label' => 'Lý do'],

            ],
        ],
    ];

    protected $quick_search = [
        'label' => 'ID, số tiền, lý do, người thực hiện, loại',
        'fields' => 'id, type, price, note, employees'
    ];

    protected $filter = [
        'admin_id' => [
            'label' => 'Người tạo',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'code',
            'model' => \App\Models\Admin::class,
            'object' => 'admin',
            'query_type' => '='
        ],
        'customer' => [
            'label' => 'Khách hàng',
            'type' => 'select2_ajax_model',
            'display_field' => 'name',
            'display_field2' => 'tel',
            'model' => User::class,
            'object' => 'user',
            'query_type' => 'custom'
        ],
        'thu_hay_chi' => [
            'label' => 'Thu hay chi',
            'type' => 'select',
            'options' => [
                '' => '',
                '>' => 'Thu',
                '<' => 'Chi',
            ],
            'query_type' => 'custom'
        ],
        'type' => [
            'label' => 'Loại',
            'type' => 'select',
            'options' => [
                '' => '',
                'luong' => 'Lương',
                'dt' => 'Đầu tư',
                'co_so_so' => 'Cơ sở số',
                'luong_kd' => 'Lương KD',
                'luong_kt' => 'Lương KT',
                'phuc_loi' => 'Phúc lợi',
                'co_so' => 'Cơ sở vật chất',
                'khac' => 'Khác',
            ],
            'query_type' => '='
        ],
        'receiving_account' => [
            'label' => 'Tk nhận',
            'type' => 'select2_model',
            'display_field' => 'name',
            'model' => Tag::class,
            'where' => 'type="bill_receipts"',
            'object' => 'tag',
            'query_type' => '='
        ],

//        'status' => [
//            'label'      => 'Tình trạng duyệt',
//            'type'       => 'select',
//            'options'    => [
//                ''           => 'Tất cả',
//                'Chưa duyệt' => 'Chưa duyệt',
//                'Đã duyệt'   => 'Đã duyệt',
//                'Tạm dừng'   => 'Tạm dừng',
//            ],
//            'query_type' => 'custom'   // bắt buộc phải là custom
//        ],
        'domain' => [
            'label' => 'Tên miền',
            'type' => 'text',
            'query_type' => 'custom'
        ],
        'filter_date' => [
            'label' => 'Lọc theo',
            'type' => 'filter_date',
            'options' => [
                '' => '',
                'date' => 'Ngày giao dịch',
                'created_at' => 'Ngày tạo',
            ],
            'query_type' => 'filter_date'
        ],
    ];

    public function quickSearch($query, $request)
    {
        if ($request->filled('quick_search')) {
            $search = $request->quick_search;
            $query->where(function ($q) use ($search) {
                foreach (explode(',', $this->quick_search['fields']) as $field) {
                    $q->orWhere(trim($field), 'like', "%{$search}%");
                }
            });
        }
        return $query;
    }

    public function appendWhere($query, $request)
    {
       if($request->status == 'Chưa duyệt'){
           $query->where('status','=',0);
       } elseif ($request->status == 'Đã duyệt'){
           $query->where('status','=',1);
       } elseif ($request->status == 'Tạm dừng'){
           $query->where('status','=',2);
       }
        if ($request->filled('month') || $request->filled('year')) {
            $month = $request->month;
            $year  = $request->year ?: date('Y');
            if ($month && $year) {
                $query->whereMonth('date', $month)->whereYear('date', $year);
            } elseif ($year) {
                $query->whereYear('date', $year);
            }
        }
        // Lọc thu / chi
        if ($request->filled('thu_hay_chi')) {
            if ($request->thu_hay_chi === '>') {
                $query->where('price', '>', 0);
            } elseif ($request->thu_hay_chi === '<') {
                $query->where('price', '<', 0);
            }
        }

        // Lọc ngày giao dịch hoặc ngày tạo
        if ($request->filled('filter_date') && $request->filled('time')) {
            $parts = explode(' - ', $request->time);
            if (count($parts) === 2) {
                $from = Carbon::createFromFormat('d/m/Y', trim($parts[0]))->startOfDay();
                $to   = Carbon::createFromFormat('d/m/Y', trim($parts[1]))->endOfDay();

                if ($request->filter_date === 'date') {
                    $query->whereBetween('date', [$from, $to]);
                } else {
                    $query->whereBetween('created_at', [$from, $to]);
                }
            }
        }



        return $query;
    }

    public function sort($request, $query)
    {
        // Sort mặc định
        if (!$request->filled('sorts')) {
            $query->orderByRaw($this->orderByRaw);
        }
        return parent::sort($request, $query);
    }

    public function list(Request $request)
    {
        $data = $this->getDataList($request);

        $items = $data['listItem']->map(function ($item) {
//            $bill = Bill::where('id', $item->bill_id)->first();
//            $name = null;
//            if ($bill && $bill->customer_id) {
//                $name = Lead::where('id', $bill->customer_id)->first();
//            }

            $tag = $item->receiving_account ? Tag::find($item->receiving_account) : null;
            return [
                'id'                    => $item->id,
                'date'                  => $item->date ? Carbon::parse($item->date)->format('d/m/Y') : '',
                'price'                 => (float)$item->price,
                'type'                  => $item->type,
                'note'                  => $item->note,
                'employees'             => $item->employees,
                'bill_id'               => $item->bill_id,
                'so_hoa_don'           => $item->so_hoa_don,
                'receiving_account_name'=> $tag->name ?? '',
                'image'                 => $item->image ? url('/filemanager/userfiles/' . $item->image) : '',
                'status'                => (int)$item->status==1 ? 'Đã duyệt' : ((int)$item->status==0 ? 'Chưa duyệt' : 'Tạm dừng'),
                'creator_name'          => $item->admin->name ?? '',
                'created_at'            => $item->created_at ? Carbon::parse($item->created_at)->format('d/m/Y H:i') : '',
//                'domain' => $bill->domain ?? '',
//                'customer' => $name->name ??'',
//                'tel' => $name->tel ??'',
             //   'tel' => $name->tel ??'',
            ];
        });

        return response()->json([
            'status'        => 'success',
            'msg'           => 'Danh sách thu chi',
            'data'          => $items,
            'current_page'  => $data['listItem']->currentPage(),
            'total_pages'   => $data['listItem']->lastPage(),
            'total_records' => $data['listItem']->total(),
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|in:0,1,2',
            // 0 = chưa duyệt, 1 = đã duyệt, 2 = tạm dừng
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $receipt = BillReceipts::find($id);

        if (!$receipt) {
            return response()->json([
                'status' => false,
                'msg' => 'Phiếu thu không tồn tại',
            ], 404);
        }

        $receipt->status = $request->status;
        $receipt->save();

        return response()->json([
            'status' => true,
            'msg' => 'Cập nhật trạng thái phiếu thu thành công',
            'data' => [
                'id' => $receipt->id,
                'status' => (int)$receipt->status,
                'status_text' => $receipt->status == 1
                    ? 'Đã duyệt'
                    : ($receipt->status == 0 ? 'Chưa duyệt' : 'Tạm dừng'),
            ],
        ]);
    }

    public function detailOrUpdate(Request $request, $id = null)
    {
        // Nếu không có ID và là POST → tạo mới
        if (!$id && $request->isMethod('post')) {
            $data = $request->only([
                'image', 'bill_id', 'status', 'note', 'admin_id', 'receiving_account','price','type'
            ]);

            $data['created_at'] = now();
            $data['updated_at'] = now();
            // Nếu có image, thêm đường dẫn đầy đủ sau
            $receiptId = DB::table('bill_receipts')->insertGetId($data);
            $receipt = DB::table('bill_receipts as br')
                ->select(
//                    'br.id',
                    'br.image',
                    'br.bill_id',
                    'br.status',
                    'br.created_at',
                    'br.note',
                    'br.admin_id',
                    'br.price',
                    'br.receiving_account',
                    'br.type',
                    'a.name as creator_name',
                    't.name as receiving_account_name'
                )
                ->leftJoin('admin as a', 'br.admin_id', '=', 'a.id')
                ->leftJoin('tags as t', function($join) {
                    $join->on('br.receiving_account', '=', 't.id')
                        ->where('t.type', 'bill_receipts');
                })
                ->where('br.id', $receiptId)
                ->first();
            $data = [
                'id' => $receipt->id,
                'image' => $receipt->image ? url('/filemanager/userfiles/' . $receipt->image ): '',
                'bill_id' => $receipt->bill_id,
                'status' => $receipt->status??'',
                'created_at' => $receipt->created_at??'',
                'note' => $receipt->note??'',
                'creator_name' => $receipt->creator_name??'',
                'receiving_account_name' =>optional( $receipt->receiving_account_name)->name??'',
                'price'=> $receipt->price??0,
                'type'=> $receipt->type??'khac',
            ];

            return response()->json([
                'status' => 'success',
                'msg' => 'Tạo phiếu thu/chi thành công',
                'data' => $data
            ], 201);
        }

        // ===== Lấy chi tiết (GET hoặc có ID) =====
        $receipt = DB::table('bill_receipts as br')
            ->select(
                'br.id',
                'br.image',
                'br.bill_id',
                'br.status',
                'br.created_at',
                'br.note',
                'br.admin_id',
                'br.price',
                'br.type',
                'br.receiving_account',
                'a.name as creator_name',
                't.name as receiving_account_name'
            )
            ->leftJoin('admin as a', 'br.admin_id', '=', 'a.id')
            ->leftJoin('tags as t', function($join) {
                $join->on('br.receiving_account', '=', 't.id')
                    ->where('t.type', 'bill_receipts');
            })
            ->where('br.id', $id)
            ->first();

        if (!$receipt) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Phiếu thu/chi không tồn tại'
            ], 404);
        }

        // ===== Cập nhật (POST/PUT với ID) =====
        if (($request->isMethod('post') || $request->isMethod('put')) && $id) {
            $data = $request->only([
                'image', 'bill_id', 'status', 'note', 'admin_id', 'receiving_account','price','type'
            ]);
            if ($request->filled('created_at')) {
                $data['created_at'] = $request->created_at; // định dạng: 'YYYY-MM-DD HH:MM:SS'
            }

            if ($request->filled('admin_id')) {
                $admin = Admin::where('name', $request->admin_id)->first();
                if ($admin) {
                    $data['admin_id'] = $admin->id;
                } else {
                    unset($data['admin_id']);
                }
            } else {
                unset($data['admin_id']);
            }
            if ($request->filled('receiving_account')) {
                $stk = Tag::where('name', $request->receiving_account)
                    ->where('type', 'bill_receipts')
                    ->first();

                if ($stk) {
                    $data['receiving_account'] = $stk->id; // Lưu id vào DB
                } else {
                    return response()->json([
                        'status' => 'error',
                        'msg' => 'Tài khoản nhận không hợp lệ'
                    ], 400);
                }
            } else {
                unset($data['receiving_account']);
            }

            $data['updated_at'] = now();
            $data['status']= $request->status =='Đã duyệt'?1:($request->status=='Chưa duyệt'?0:2);
            DB::table('bill_receipts')->where('id', $id)->update($data);
            // Lấy lại dữ liệu mới
            $receipt = DB::table('bill_receipts as br')
                ->select(
                    'br.id',
                    'br.image',
                    'br.bill_id',
                    'br.status',
                    'br.created_at',
                    'br.note',
                    'br.admin_id',
                    'br.receiving_account',
                    'br.price',
                    'br.type',
                    'a.name as creator_name',
                    't.name as receiving_account_name'
                )
                ->leftJoin('admin as a', 'br.admin_id', '=', 'a.id')
                ->leftJoin('tags as t', function($join) {
                    $join->on('br.receiving_account', '=', 't.id')
                        ->where('t.type', 'bill_receipts');
                })
                ->where('br.id', $id)
                ->first();
        }

        $data = [
            'id' => $receipt->id,
            'image' => $receipt->image ? url('/filemanager/userfiles/' . $receipt->image) : '',
            'bill_id' => $receipt->bill_id,
            'status' => $receipt->status==1 ? 'Đã duyệt' : ($receipt->status==0 ? 'Chưa duyệt' : 'Tạm dừng'),
            'created_at' => $receipt->created_at,
            'note' => $receipt->note,
            'creator_name' => $receipt->creator_name,
            'receiving_account' => $receipt->receiving_account_name,
'price'=>$receipt->price??0,
'type'=>$receipt->type??'khac',

        ];

        return response()->json([
            'status' => 'success',
            'msg' => ($request->isMethod('post') || $request->isMethod('put')) ? 'Cập nhật thành công' : 'Chi tiết phiếu thu/chi',
            'data' => $data
        ]);
    }
    public function destroy($id)
    {
        $receipt = DB::table('bill_receipts')->where('id', $id)->first();

        if (!$receipt) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Phiếu thu/chi không tồn tại'
            ], 404);
        }

        DB::table('bill_receipts')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'msg' => 'Xóa phiếu thu/chi thành công'
        ]);
    }







}
