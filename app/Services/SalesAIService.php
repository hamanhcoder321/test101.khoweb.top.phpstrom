<?php
namespace App\Services;

use App\OpenAIService;

class SalesAIService
{
    /** @var OpenAIService */
    protected $ai;

    // System prompt chung cho AI – áp dụng cho mọi cuộc hội thoại
    const SYSTEM_PROMPT = "Bạn là TRỢ LÝ AI thông minh trong hệ thống CRM, hỗ trợ nhân viên sales và chăm sóc khách hàng.

NGUYÊN TẮC QUAN TRỌNG:
- Luôn phản hồi bằng tiếng Việt, ngắn gọn, tự nhiên như người thật
- Nếu câu hỏi không rõ nghĩa → hỏi lại: 'Bạn có thể nói rõ hơn về...' hoặc 'Ý bạn là...?'
- Nếu không có dữ liệu liên quan → nói thật: 'Tôi không tìm thấy thông tin về...'
- Trả lời được MỌI câu hỏi: CRM, sales, marketing, kỹ thuật, kiến thức chung
- Không bịa số liệu, không đoán mò khi không có dữ liệu thực
- Ưu tiên thực tế, hành động cụ thể hơn lý thuyết chung chung
- KHÔNG BAO GIỜ trả lời trống hoặc vô nghĩa";

    public function __construct()
    {
        $this->ai = new OpenAIService();
    }

    /**
     * Hỏi về 1 lead theo lead_id
     */
    public function askLead(array $lead, string $question): string
    {
        $userMsg = $this->buildLeadPrompt($lead, $question);
        return $this->ai->chat($userMsg, self::SYSTEM_PROMPT, 1200);
    }

    /**
     * Hỏi về khách hàng theo SĐT – context đa bảng
     */
    public function askByPhone(array $ctx, string $question): string
    {
        $userMsg = $this->buildPhonePrompt($ctx, $question);
        return $this->ai->chat($userMsg, self::SYSTEM_PROMPT, 1500);
    }

    /**
     * Hỏi tự do – không cần context khách hàng
     */
    public function askGeneral(string $question): string
    {
        // Hỏi tự do: AI trả lời bất kỳ câu hỏi gì
        return $this->ai->chat($question, self::SYSTEM_PROMPT, 1000);
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────

    private function buildLeadPrompt(array $lead, string $question): string
    {
        $fields = [];
        $map = [
            'Tên khách'          => $lead['name'] ?? '',
            'SĐT'                => $lead['phone'] ?? '',
            'Email'              => $lead['email'] ?? '',
            'Khu vực'            => $lead['location'] ?? '',
            'Nguồn lead'         => $lead['source'] ?? '',
            'Dịch vụ quan tâm'   => $lead['service'] ?? '',
            'Nhu cầu'            => $lead['need'] ?? '',
            'Trạng thái'         => $lead['status'] ?? '',
            'Mức quan tâm'       => $lead['rate'] ?? '',
            'Lần liên hệ cuối'   => $lead['last_contact'] ?? '',
            'Sản phẩm tư vấn'    => $lead['product'] ?? '',
            'Lý do từ chối'      => $lead['reason_refusal'] ?? '',
            'Công ty'            => $lead['company'] ?? '',
            'Tag'                => $lead['tags'] ?? '',
        ];
        foreach ($map as $label => $value) {
            if (!empty(trim($value))) {
                $fields[] = "• {$label}: {$value}";
            }
        }
        $info = $fields ? implode("\n", $fields) : '(Không có thông tin)';

        return "THÔNG TIN KHÁCH HÀNG:\n{$info}\n\nCÂU HỎI: {$question}";
    }

    private function buildPhonePrompt(array $ctx, string $question): string
    {
        $tel         = $ctx['tel'] ?? '';
        $user        = $ctx['user'] ?? null;
        $canSeeMoney = $ctx['can_see_money'] ?? false;

        // ── Thông tin khách hàng đã ký (bảng users) ─────────────
        $userSection = "Chưa có hợp đồng chính thức trong hệ thống.";
        if ($user) {
            $userSection = "• Tên: {$user->name}\n• SĐT: {$user->tel}\n• Email: " . ($user->email ?? '') . "\n• Ngày đăng ký: " . ($user->created_at ?? '');
        }

        // ── Leads (khách tiềm năng) ───────────────────────────────
        $leadsSection = "Không có thông tin lead.";
        if (!empty($ctx['leads'])) {
            $lines = [];
            foreach ($ctx['leads'] as $i => $l) {
                $l = (object)$l;
                $parts = array_filter([
                    $l->name ?? '',
                    $l->status  ? "trạng thái: {$l->status}"  : '',
                    $l->rate    ? "mức quan tâm: {$l->rate}"  : '',
                    $l->service ? "dịch vụ: {$l->service}"    : '',
                    $l->need    ? "nhu cầu: " . mb_substr($l->need, 0, 100) : '',
                ]);
                $lines[] = "  #" . ($i+1) . ": " . implode(' | ', $parts);
            }
            $leadsSection = implode("\n", $lines);
        }

        // ── Lịch sử chăm sóc ─────────────────────────────────────
        $logSection = "Chưa có lịch sử chăm sóc.";
        if (!empty($ctx['contact_logs'])) {
            $lines = [];
            foreach ($ctx['contact_logs'] as $log) {
                $log   = (object)$log;
                $title = !empty($log->title) ? "[{$log->title}] " : '';
                $note  = mb_substr($log->note ?? '', 0, 150);
                $admin = $log->admin_name ?? 'Sale';
                $lines[] = "  [{$log->created_at}] {$admin}: {$title}{$note}";
            }
            $logSection = implode("\n", $lines);
        }

        // ── Hợp đồng ─────────────────────────────────────────────
        $billsSection = "Không có hợp đồng.";
        if (!empty($ctx['bills'])) {
            $lines = [];
            foreach ($ctx['bills'] as $b) {
                $b = (object)$b;
                $line = "  • {$b->domain} | ký: {$b->registration_date} | hết hạn: {$b->expiry_date} | tình trạng: {$b->status}";
                if ($canSeeMoney && isset($b->total_price)) {
                    $line .= " | giá: " . number_format($b->total_price, 0, '.', '.') . "đ";
                    $line .= " | đã thu: " . number_format($b->total_received ?? 0, 0, '.', '.') . "đ";
                }
                if (!empty($b->customer_note)) {
                    $line .= " | ghi chú: {$b->customer_note}";
                }
                $lines[] = $line;
            }
            $billsSection = implode("\n", $lines);
        }

        // ── Lịch sử thanh toán ───────────────────────────────────
        $receiptSection = '';
        if ($canSeeMoney) {
            $receiptSection = "\n[LỊCH SỬ THANH TOÁN]\n";
            if (!empty($ctx['bill_receipts'])) {
                $lines = [];
                foreach ($ctx['bill_receipts'] as $r) {
                    $r = (object)$r;
                    $lines[] = "  [{$r->date}] " . number_format($r->price, 0, '.', '.') . "đ | ghi chú: {$r->note}";
                }
                $receiptSection .= implode("\n", $lines);
            } else {
                $receiptSection .= "Chưa có lịch sử thanh toán.";
            }
        }

        if (!$canSeeMoney) {
            $receiptSection = "\n[LỊCH SỬ THANH TOÁN]\n(Bạn không có quyền xem thông tin tài chính)";
        }

        return "DỮ LIỆU KHÁCH HÀNG - SĐT: {$tel}

[THÔNG TIN KÝ HỢP ĐỒNG]
{$userSection}

[THÔNG TIN LEAD TIỀM NĂNG]
{$leadsSection}

[LỊCH SỬ CHĂM SÓC - 5 gần nhất]
{$logSection}

[HỢP ĐỒNG]
{$billsSection}
{$receiptSection}

CÂU HỎI: {$question}";
    }
}
