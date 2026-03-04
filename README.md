# IMPORT TÊN MIỀN VÀO HỢP ĐỒNG (Nhanhoa)
> Cập nhật: 2026-03-01



## 1. ĐƯỜNG DẪN CHỨC NĂNG


/admin/import/add_nhanhoa




## 2. QUY TRÌNH (3 BƯỚC)


[Bước 1] Upload file Excel
       ↓
[Bước 2] Xem trước dữ liệu (preview)
       ↓
[Bước 3] Xác nhận → Lưu vào bảng bills (hợp đồng)




## 3. FILE EXCEL CẦN CÓ CÁC CỘT

| Tên cột Excel (Nhanhoa VN) | Tên cột khác (quốc tế) | Ý nghĩa |

| `tn_min` / `ten_mien` | `domain`, `domain_name` | Tên miền (**bắt buộc**) |
| `ngp_ng_k` / `ngy_ng_k` | `registration_date`, `start_date` | Ngày đăng ký |
| `ngp_ht_hn` / `ngy_ht_hn` | `expiry_date`, `end_date` | Ngày hết hạn |

> Hỗ trợ định dạng ngày: `dd/mm/yyyy`, `dd-mm-yyyy`, `yyyy-mm-dd`



## 4. GIÁ TRỊ MẶC ĐỊNH KHI TẠO HỢP ĐỒNG

| Cột trong `bills` | Giá trị | Ghi chú |

| `service_id` | **16** | Loại dịch vụ = Tên miền |
| `status` | **1** | Trạng thái kích hoạt |
| `auto_extend` | **0** | Không tự gia hạn |
| `total_price` | **0** | Chưa có giá |
| `exp_price` | **1** | Giá gia hạn mặc định |
| `saler_id` | Lấy theo SĐT `0987519120` từ bảng `admins` |
| `customer_id` | Lấy theo SĐT `0987519120` từ bảng `users` |
| `contract_time` | Tính tự động: `expiry_date - registration_date` (đơn vị: tháng) |
| `domain` | Lấy từ cột tên miền trong Excel |



## 5. LOGIC BỎ QUA (SKIP) KHI IMPORT

Một dòng Excel **bị bỏ qua** (không import) nếu:


domain đó đã tồn tại trong bảng bills
(kiểm tra cột `domain` theo giá trị đã chuẩn hóa)


> Chuẩn hóa domain: xóa `http://`, `https://`, `www.`, chuyển thường, bỏ `/` cuối



## 6. CÁC FILE LIÊN QUAN

### Controller chính – Import tên miền Nhanhoa

app/Http/Controllers/Admin/ImportController.php

  addNhanhoa(Request $request)
  │   GET  → Hiển thị form upload (Bước 1)
  │   POST action=upload    → Đọc Excel, lưu session, chuyển sang preview (Bước 2)
  │   POST action=save_bills → Lưu hợp đồng vào DB (Bước 3)
  │
  └── saveNhanhoaAsBill(array $row, $saler_id, $customer_id)
          Xử lý từng dòng Excel:
          - Chuẩn hóa domain
          - Kiểm tra trùng theo cột `domain`
          - Parse ngày đăng ký, ngày hết hạn
          - Tính contract_time (tháng)
          - INSERT vào bảng `bills`


### Controller hợp đồng – Danh sách & Lọc

app/Modules/HBBill/Controllers/Admin/BillController.php

  appendWhere($query, $request)
  └── Lọc "Chưa thanh toán hết":
      WHERE (total_received IS NULL
         OR total_received < total_price_contract)
      AND id NOT IN (danh sách hợp đồng bỏ)


### Model

app/CRMDV/Models/Bill.php          ← Model hợp đồng (dùng trong saveNhanhoaAsBill)
app/Modules/HBBill/Models/Bill.php ← Model hợp đồng (dùng trong BillController)
app/Models/Admin.php               ← Lấy saler_id theo SĐT
app/Models/User.php                ← Lấy customer_id theo SĐT


### Route

app/Http/routes.php  hoặc  routes/web.php

GET  /admin/import/add_nhanhoa   → addNhanhoa (hiển thị form)
POST /admin/import/add_nhanhoa   → addNhanhoa (xử lý upload & lưu)


### View

resources/views/admin/themes/metronic1/import/add_nhanhoa.blade.php
  Bước 1: Form upload file
  Bước 2: Bảng preview dữ liệu từ Excel
  Bước 3: Nút xác nhận lưu




## 7. VÍ DỤ FILE EXCEL MẪU

| tn_min | ngp_ng_k | ngp_ht_hn |
||||
| hoamart.com.vn | 01/01/2024 | 01/01/2025 |
| example.vn | 15/06/2023 | 15/06/2026 |



## 8. LƯU Ý

- Tối đa **500 bản ghi** / 1 lần import
- File hỗ trợ: `.xlsx`, `.xls`, `.csv` (tối đa 5MB)
- Nếu domain đã có → **bỏ qua** (không ghi đè, không báo lỗi)
- Nếu không có ngày đăng ký → dùng `ngày hôm nay` làm mặc định



- modules AI: Muốn dùng sang dự án khác: chỉ cần copy thư mục app/Modules/AI/, đăng ký ChatAIServiceProvider trong config/app.php, thêm .env keys
