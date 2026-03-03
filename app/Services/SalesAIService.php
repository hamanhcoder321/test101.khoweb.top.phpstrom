<?php
namespace App\Services;

use App\OpenAIService;

class SalesAIService
{
    /** @var OpenAIService */
    protected $ai;

    public function __construct()
    {
        $this->ai = new OpenAIService();
    }

    /**
     * Hỏi về 1 lead theo lead_id (cũ - giữ nguyên)
     */
    public function askLead(array $lead, string $question): string
    {
        $prompt = $this->buildPrompt($lead, $question);
        return $this->ai->chat($prompt);
    }

    /**
     * Hỏi về khách hàng theo SĐT – context đa bảng
     */
    public function askByPhone(array $ctx, string $question): string
    {
        $prompt = $this->buildPhonePrompt($ctx, $question);
        return $this->ai->chat($prompt, null, 1500);
    }

    /**
     * Hỏi tự do – không cần context khách hàng
     */
    public function askGeneral(string $question): string
    {
        $prompt = "Bạn là TRỢ LÝ AI cho nhân viên SALE trong hệ thống CRM.\nHãy trả lời câu hỏi dưới đây một cách ngắn gọn, thực tế, hữu ích.\n\nCÂU HỎI: {$question}";
        return $this->ai->chat($prompt, null, 1000);
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────

    private function buildPrompt(array $lead, string $question): string
    {
        return "
Bạn là TRỢ LÝ AI cho TELESALE trong hệ thống CRM.

NGUYÊN TẮC:
- Chỉ dùng dữ liệu lead được cung cấp
- Không bịa, không suy đoán ngoài dữ liệu
- Trả lời như một nhân viên sales kinh nghiệm
- Ưu tiên hành động thực tế: gọi lại, tư vấn, xử lý từ chối

THÔNG TIN LEAD:
Tên khách: {$lead['name']}
SĐT: {$lead['phone']}
Email: {$lead['email']}
Khu vực: {$lead['location']}
Nguồn lead: {$lead['source']}
Dịch vụ quan tâm: {$lead['service']}
Dự án: {$lead['project']}
Chủ đề: {$lead['topic']}
Nhu cầu: {$lead['need']}
Trạng thái: {$lead['status']}
Mức độ quan tâm: {$lead['rate']}
Lần liên hệ cuối: {$lead['last_contact']}
Sản phẩm tư vấn: {$lead['product']}
Ưu đãi: {$lead['discount']}
Lý do từ chối: {$lead['reason_refusal']}
Ngày nhận lead: {$lead['received_date']}
Tag: {$lead['tags']}
Công ty: {$lead['company']}

CÂU HỎI CỦA TELESALE:
{$question}
";
    }

    private function buildPhonePrompt(array $ctx, string $question): string
    {
        $tel  = $ctx['tel'] ?? '';
        $user = $ctx['user'] ?? null;

        // ── Thông tin khách hàng đã ký (bảng users) ─────────────
        $userSection = "KHÔNG CÓ (chưa ký hợp đồng)";
        if ($user) {
            $userSection = implode("\n", [
                "  Tên: {$user->name}",
                "  SĐT: {$user->tel}",
                "  Email: " . ($user->email ?? ''),
                "  Ngày đăng ký: " . ($user->created_at ?? ''),
            ]);
        }

        // ── Leads (khách tiềm năng) ───────────────────────────────
        $leadsSection = "KHÔNG CÓ";
        if (!empty($ctx['leads'])) {
            $lines = [];
            foreach ($ctx['leads'] as $i => $l) {
                $l = (object)$l;
                $lines[] = "  Lead #" . ($i+1) . ": {$l->name} | trạng thái: {$l->status} | mức quan tâm: {$l->rate} | dịch vụ: {$l->service} | nhu cầu: {$l->need} | lần liên hệ cuối: {$l->contacted_log_last}";
            }
            $leadsSection = implode("\n", $lines);
        }

        // ── Lịch sử chăm sóc (lead_contacted_log) ────────────────
        $logSection = "KHÔNG CÓ";
        if (!empty($ctx['contact_logs'])) {
            $lines = [];
            foreach ($ctx['contact_logs'] as $log) {
                $log = (object)$log;
                $title = !empty($log->title) ? "[{$log->title}] " : '';
                $note  = mb_substr($log->note ?? '', 0, 200);
                $lines[] = "  [{$log->created_at}] {$log->admin_name}: {$title}{$note}";
            }
            $logSection = implode("\n", $lines);
        }

        // ── Hợp đồng (bills) ─────────────────────────────────────
        $billsSection = "KHÔNG CÓ";
        if (!empty($ctx['bills'])) {
            $lines = [];
            foreach ($ctx['bills'] as $b) {
                $b = (object)$b;
                $tongTien   = number_format($b->total_price, 0, '.', '.');
                $daThanhToan = number_format($b->total_received ?? 0, 0, '.', '.');
                $lines[] = "  HĐ #{$b->id}: {$b->domain} | {$tongTien}đ | đã thu: {$daThanhToan}đ | ký: {$b->registration_date} | hết hạn: {$b->expiry_date} | ghi chú: {$b->customer_note}";
            }
            $billsSection = implode("\n", $lines);
        }

        // ── Lịch sử thanh toán (bill_receipts) ───────────────────
        $receiptSection = "KHÔNG CÓ";
        if (!empty($ctx['bill_receipts'])) {
            $lines = [];
            foreach ($ctx['bill_receipts'] as $r) {
                $r = (object)$r;
                $so = number_format($r->price, 0, '.', '.');
                $lines[] = "  [{$r->date}] {$so}đ | HĐ #{$r->bill_id} | ghi chú: {$r->note}";
            }
            $receiptSection = implode("\n", $lines);
        }

        return "
Bạn là TRỢ LÝ AI cho SALE trong hệ thống CRM.

NGUYÊN TẮC:
- Chỉ dùng dữ liệu được cung cấp bên dưới, không bịa thêm
- Trả lời ngắn gọn, rõ ràng, thực tế
- Nếu không có dữ liệu liên quan thì nói rõ là không có thông tin

======= GIẢI THÍCH CÁC BẢNG DỮ LIỆU =======
• Bảng USERS     : Lưu thông tin khách hàng ĐÃ KÝ hợp đồng
• Bảng LEADS     : Lưu thông tin khách hàng TIỀM NĂNG (quan tâm nhưng chưa ký)
• Bảng LEAD_CONTACTED_LOG : Lịch sử chăm sóc khách – sale ghi chú mỗi lần liên hệ
• Bảng BILLS     : Danh sách hợp đồng đã ký của khách
• Bảng BILL_RECEIPTS : Lịch sử thanh toán tiền hợp đồng của khách

======= DỮ LIỆU KHÁCH HÀNG SĐT: {$tel} =======

[KHÁCH HÀNG ĐÃ KÝ HỢP ĐỒNG - bảng users]
{$userSection}

[KHÁCH HÀNG TIỀM NĂNG - bảng leads]
{$leadsSection}

[LỊCH SỬ CHĂM SÓC - bảng lead_contacted_log]
{$logSection}

[HỢP ĐỒNG - bảng bills]
{$billsSection}

[LỊCH SỬ THANH TOÁN - bảng bill_receipts]
{$receiptSection}

======= CÂU HỎI CỦA SALE =======
{$question}
";
    }
}
