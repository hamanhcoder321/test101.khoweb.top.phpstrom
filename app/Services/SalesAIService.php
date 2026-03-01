<?php
namespace App\Services;

use App\OpenAIService;

class SalesAIService
{
    protected OpenAIService $ai;

    public function __construct()
    {
// Inject AI core
        $this->ai = new OpenAIService();
    }

    /**
     * Hàm chính để sales hỏi về 1 lead
     */
    public function askLead(array $lead, string $question): string
    {
        $prompt = $this->buildPrompt($lead, $question);

        return $this->ai->chat($prompt);
    }

    /**
     * Build prompt telesales
     * Đây là phần QUAN TRỌNG NHẤT
     */
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
}
