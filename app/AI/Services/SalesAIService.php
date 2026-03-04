<?php
namespace App\AI\Services;

class SalesAIService
{
    /** @var OpenAIService */
    protected $ai;

    const SYSTEM_PROMPT = "Bạn là chuyên gia sales AI trong hệ thống CRM, hỗ trợ nhân viên bán hàng.

CÁCH TRẢ LỜI:
- Trả lời ĐÚNG câu hỏi, NGẮN GỌN, tự nhiên như người thật
- Hỏi gì trả lời đó — không cần thêm nhận định, giải thích thừa
- Khi có dữ liệu khách hàng → dựa vào đó trả lời ngay, không hỏi lại
- Khi được hỏi 'gợi ý', 'tư vấn gì', 'nên làm gì' → đưa ra ngay 2-3 bước cụ thể
- Soạn tin nhắn/kịch bản khi được yêu cầu — viết luôn, không giải thích
- Chỉ hỏi lại khi câu hỏi thực sự không có đủ thông tin để trả lời
- Không dùng bullet point dài dòng khi câu trả lời ngắn là đủ";

    public function __construct()
    {
        $this->ai = new OpenAIService();
    }

    public function askLead(array $lead, string $question): string
    {
        return $this->ai->chat($this->buildLeadPrompt($lead, $question), self::SYSTEM_PROMPT, 1200);
    }

    public function askByPhone(array $ctx, string $question): string
    {
        return $this->ai->chat($this->buildPhonePrompt($ctx, $question), self::SYSTEM_PROMPT, 1500);
    }

    public function askGeneral(string $question): string
    {
        return $this->ai->chat($question, self::SYSTEM_PROMPT, 1000);
    }

    private function buildLeadPrompt(array $lead, string $question): string
    {
        $fields = [];
        $map = [
            'Tên khách'        => $lead['name'] ?? '',
            'SĐT'              => $lead['phone'] ?? '',
            'Email'            => $lead['email'] ?? '',
            'Khu vực'          => $lead['location'] ?? '',
            'Nguồn lead'       => $lead['source'] ?? '',
            'Dịch vụ quan tâm' => $lead['service'] ?? '',
            'Nhu cầu'          => $lead['need'] ?? '',
            'Trạng thái'       => $lead['status'] ?? '',
            'Mức quan tâm'     => $lead['rate'] ?? '',
            'Lần liên hệ cuối' => $lead['last_contact'] ?? '',
            'Sản phẩm tư vấn'  => $lead['product'] ?? '',
            'Lý do từ chối'    => $lead['reason_refusal'] ?? '',
            'Công ty'          => $lead['company'] ?? '',
            'Tag'              => $lead['tags'] ?? '',
        ];
        foreach ($map as $label => $value) {
            if (!empty(trim($value))) $fields[] = "• {$label}: {$value}";
        }
        $info = $fields ? implode("\n", $fields) : '(Không có thông tin)';
        return "THÔNG TIN KHÁCH HÀNG:\n{$info}\n\nCÂU HỎI: {$question}";
    }

    private function buildPhonePrompt(array $ctx, string $question): string
    {
        $tel         = $ctx['tel'] ?? '';
        $user        = $ctx['user'] ?? null;
        $canSeeMoney = $ctx['can_see_money'] ?? false;

        $userSection = "Chưa có hợp đồng chính thức trong hệ thống.";
        if ($user) {
            $userSection = "• Tên: {$user->name}\n• SĐT: {$user->tel}\n• Email: " . ($user->email ?? '') . "\n• Ngày đăng ký: " . ($user->created_at ?? '');
        }

        $leadsSection = "Không có thông tin lead.";
        if (!empty($ctx['leads'])) {
            $lines = [];
            foreach ($ctx['leads'] as $i => $l) {
                $l = (object)$l;
                $parts = array_filter([
                    $l->name ?? '',
                    $l->status  ? "trạng thái: {$l->status}" : '',
                    $l->rate    ? "mức quan tâm: {$l->rate}" : '',
                    $l->service ? "dịch vụ: {$l->service}"   : '',
                    $l->need    ? "nhu cầu: " . mb_substr($l->need, 0, 100) : '',
                ]);
                $lines[] = "  #" . ($i+1) . ": " . implode(' | ', $parts);
            }
            $leadsSection = implode("\n", $lines);
        }

        $logSection = "Chưa có lịch sử chăm sóc.";
        if (!empty($ctx['contact_logs'])) {
            $lines = [];
            foreach ($ctx['contact_logs'] as $log) {
                $log = (object)$log;
                $title = !empty($log->title) ? "[{$log->title}] " : '';
                $note  = mb_substr($log->note ?? '', 0, 150);
                $lines[] = "  [{$log->created_at}] " . ($log->admin_name ?? 'Sale') . ": {$title}{$note}";
            }
            $logSection = implode("\n", $lines);
        }

        $billsSection = "Không có hợp đồng.";
        if (!empty($ctx['bills'])) {
            $lines = [];
            foreach ($ctx['bills'] as $b) {
                $b    = (object)$b;
                $line = "  • {$b->domain} | ký: {$b->registration_date} | hết hạn: {$b->expiry_date} | tình trạng: {$b->status}";
                if ($canSeeMoney && isset($b->total_price)) {
                    $line .= " | giá: " . number_format($b->total_price, 0, '.', '.') . "đ";
                    $line .= " | đã thu: " . number_format($b->total_received ?? 0, 0, '.', '.') . "đ";
                }
                if (!empty($b->customer_note)) $line .= " | ghi chú: {$b->customer_note}";
                $lines[] = $line;
            }
            $billsSection = implode("\n", $lines);
        }

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
        } else {
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
