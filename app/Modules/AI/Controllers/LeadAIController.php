<?php
namespace App\Modules\AI\Controllers;
use App\CRMDV\Models\Lead;
use App\Services\SalesAIService;
use App\Http\Helpers\CommonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LeadAIController extends Controller
{
    // Danh sách role được xem dữ liệu tiền nong
    protected $roleCanSeeMoney = [
        'super_admin', 'sale', 'cskh',
        'truong_phong_sale', 'giam_doc_kinh_doanh', 'ke_toan'
    ];

    public function ask(Request $request)
    {
        try {
            $lead = Lead::findOrFail($request->lead_id);

            $leadData = [
                'name'           => $lead->name ?? '',
                'phone'          => $lead->tel ?? '',
                'email'          => $lead->email ?? '',
                'location'       => $lead->tinh ?? '',
                'source'         => $lead->source ?? '',
                'service'        => $lead->service ?? '',
                'project'        => $lead->project ?? '',
                'topic'          => $lead->topic ?? '',
                'need'           => $lead->need ?? '',
                'status'         => $lead->status ?? '',
                'rate'           => $lead->rate ?? '',
                'last_contact'   => $lead->contacted_log_last ?? '',
                'product'        => $lead->product ?? '',
                'discount'       => $lead->discount ?? '',
                'reason_refusal' => $lead->reason_refusal ?? '',
                'received_date'  => $lead->received_date ?? '',
                'tags'           => $lead->tags ?? '',
                'company'        => $lead->company ?? '',
            ];

            $ai = new SalesAIService();
            $answer = $ai->askLead($leadData, $request->question);

            return response()->json(['answer' => $answer]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tra cứu khách hàng theo SĐT, query 5 bảng, đưa context cho AI
     * Có phân quyền: chỉ role có quyền mới thấy dữ liệu tiền nong
     */
    public function askByPhone(Request $request)
    {
        try {
            $tel      = preg_replace('/\D/', '', trim($request->tel ?? ''));
            $question = trim($request->question ?? '');

            if (empty($tel)) {
                return response()->json(['error' => 'Vui lòng nhập số điện thoại'], 400);
            }

            // ── Kiểm tra quyền xem dữ liệu tiền nong ────────────────────────
            $adminUser   = Auth::guard('admin')->user();
            $roleName    = $adminUser
                ? CommonHelper::getRoleName($adminUser->id, 'name')
                : '';
            $canSeeMoney = in_array($roleName, $this->roleCanSeeMoney);

            // ── 1. LEADS (khách hàng tiềm năng) ──────────────────────────────
            $leads = DB::table('leads')
                ->where('tel', $tel)
                ->orWhere('tel', ltrim($tel, '0'))
                ->get(['id','name','tel','email','status','rate','source',
                       'service','need','company','tinh','contacted_log_last',
                       'reason_refusal','received_date','tags']);

            // ── 2. LEAD_CONTACTED_LOG (lịch sử chăm sóc) ─────────────────────
            $contactLogs = collect();
            if ($leads->isNotEmpty()) {
                $leadIds = $leads->pluck('id')->toArray();
                $contactLogs = DB::table('lead_contacted_log as l')
                    ->leftJoin('admin as a', 'a.id', '=', 'l.admin_id')
                    ->whereIn('l.lead_id', $leadIds)
                    ->orderBy('l.created_at', 'desc')
                    ->limit(5)
                    ->get(['l.lead_id', 'l.note', 'l.title', 'l.created_at', 'a.name as admin_name']);
            }

            // ── 3. USERS (khách đã ký hợp đồng) ──────────────────────────────
            $user = DB::table('users')
                ->where('tel', $tel)
                ->orWhere('tel', ltrim($tel, '0'))
                ->first(['id','name','tel','email','created_at']);

            // ── 4. BILLS (hợp đồng) ───────────────────────────────────────────
            $bills        = collect();
            $billReceipts = collect();
            if ($user) {
                // Cột an toàn – ai cũng thấy
                $billCols = ['id','domain','service_id','registration_date',
                             'expiry_date','status','auto_extend','customer_note'];

                // Cột tiền – chỉ role có quyền mới thấy
                if ($canSeeMoney) {
                    $billCols = array_merge($billCols, ['total_price','total_received','account_note']);
                }

                $bills = DB::table('bills')
                    ->where('customer_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('registration_date', 'desc')
                    ->limit(8)
                    ->get($billCols);

                // ── 5. BILL_RECEIPTS – chỉ query nếu có quyền xem tiền ────────
                if ($canSeeMoney && $bills->isNotEmpty()) {
                    $billIds = $bills->pluck('id')->toArray();
                    $billReceipts = DB::table('bill_receipts')
                        ->whereIn('bill_id', $billIds)
                        ->whereNull('deleted_at')
                        ->orderBy('date', 'desc')
                        ->limit(10)
                        ->get(['bill_id','price','date','note','so_hoa_don']);
                }
            }

            // ── Kiểm tra không tìm thấy ──────────────────────────────────────
            if ($leads->isEmpty() && !$user) {
                return response()->json([
                    'answer'  => "❌ Không tìm thấy khách hàng với SĐT: {$tel}. Vui lòng kiểm tra lại số điện thoại.",
                    'summary' => null,
                ]);
            }

            // ── Build summary để hiển thị ngay trên chat ─────────────────────
            $customerName = $user->name ?? ($leads->first()->name ?? 'Không rõ tên');
            $summary = sprintf(
                "✅ Tìm thấy: **%s** | %d hợp đồng | %d lần liên hệ%s",
                $customerName,
                $bills->count(),
                $contactLogs->count(),
                $canSeeMoney ? ' | ' . $billReceipts->count() . ' thanh toán' : ''
            );

            // ── Build context cho AI ──────────────────────────────────────────
            $context = [
                'tel'           => $tel,
                'leads'         => $leads->toArray(),
                'contact_logs'  => $contactLogs->toArray(),
                'user'          => $user,
                'bills'         => $bills->toArray(),
                'bill_receipts' => $billReceipts->toArray(),
                'can_see_money' => $canSeeMoney,
            ];

            $ai     = new SalesAIService();
            $answer = $ai->askByPhone($context, $question);

            return response()->json([
                'answer'  => $answer,
                'summary' => $summary,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hỏi AI tự do – không cần SĐT hay lead_id
     */
    public function askGeneral(Request $request)
    {
        try {
            $question = trim($request->question ?? '');
            if (empty($question)) {
                return response()->json(['error' => 'Vui lòng nhập câu hỏi'], 400);
            }

            $ai     = new SalesAIService();
            $answer = $ai->askGeneral($question);

            return response()->json(['answer' => $answer]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
