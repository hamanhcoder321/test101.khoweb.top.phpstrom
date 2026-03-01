<div class="custom-card p-4 mb-4" style="box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; background: #fff;">
    <div class="kt-portlet__head" style="border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 25px;">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title bold uppercase" style="color: #1e293b; font-size: 1.25rem; font-weight: 600; margin: 0; display: flex; align-items: center;">
                <i class="fas fa-exclamation-triangle" style="color: #e74c3c; margin-right: 10px; font-size: 22px;"></i>
                Dự án đã hoàn thành mà chưa thu tiền
            </h3>
            <?php
            $don_chua_thu_tien = \App\Modules\HBDashboard\Models\Bill::rightJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
                ->select('bills.id', 'bills.domain', 'bills.total_price_contract', 'bills.total_received', 'bills.customer_id',
                    'bill_progress.dh_id', 'bill_progress.kt_id', 'bills.registration_date', 'bills.saler_id', 'bill_progress.status');

            if(!in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin', 'truong_phong_sale'])) {
                //  nếu ko phải super_admin thì chỉ xem được hợp đồng của mình tạo hoặc mình theo dõi
                $don_chua_thu_tien = $don_chua_thu_tien->where(function ($query) {
                    $query->orWhere('saler_id', \Auth::guard('admin')->user()->id);   //  hđ của mình
                    $query->orWhere('staff_care', 'like', '%|' . \Auth::guard('admin')->user()->id . '|%');   //  hđ mình theo dõi
                });
            }
//            $don_chua_thu_tien = $don_chua_thu_tien->where('bills.id', 1593);

            $don_chua_thu_tien = $don_chua_thu_tien
                ->whereIn('bill_progress.status', ['Khách xác nhận xong', 'Tạm dừng', 'Kết thúc'])
                ->where(function ($query) {
                    $query->whereRaw('bills.total_price_contract != bills.total_received')
                        ->orWhereNull('bills.total_received');
                })
                ->orderBy('bills.saler_id', 'ASC')->orderBy('bills.registration_date', 'ASC')->get();
//            dd($don_chua_thu_tien);
            ?>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="kt-widget12">
            <div class="kt-widget12__content" style="padding-bottom:0 !important;">
                <div style="overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                    <table class="table table-striped" style="margin-bottom: 0; background: #fff; border-collapse: separate; border-spacing: 0;">
                        <thead class="kt-datatable__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <tr>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-globe" style="margin-right: 8px;"></i>Tên miền
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: right; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-money-bill-wave" style="margin-right: 8px;"></i>Tổng tiền
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: right; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-check-circle" style="margin-right: 8px; color: #2ecc71;"></i>Đã thu
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: right; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-exclamation-circle" style="margin-right: 8px; color: #f39c12;"></i>Chưa thu
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>Ngày ký
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-user-tie" style="margin-right: 8px;"></i>Thông tin
                            </th>
                        </tr>
                        </thead>
                        <tbody class="kt-datatable__body ps ps--active-y">
                        <?php $tong_tien_chua_thu = 0; $index = 0;?>
                        @foreach($don_chua_thu_tien as $v)
                            @if($v->total_price_contract != $v->total_received)
                                    <?php
                                    //  tính lại tổng thu, cho phép tính các phiếu chưa duyêt
                                    $tong_thu = \App\Modules\HBDashboard\Models\BillReceipts::where('bill_id', $v->id)->where('price', '>', 0)->sum('price');
                                    $index++;
                                    ?>
                                @if($v->total_price_contract > $tong_thu)
                                    <tr style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease; {{ $index % 2 == 0 ? 'background-color: #f8f9fc;' : 'background-color: #fff;' }}"
                                        onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                                        onmouseout="this.style.backgroundColor='{{ $index % 2 == 0 ? '#f8f9fc' : '#fff' }}'; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                                        <td style="padding: 16px; vertical-align: middle; border: none;">
                                            <a href="/admin/bill/edit/{{ $v->id }}" target="_blank"
                                               style="color: #3498db; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; transition: color 0.3s ease;"
                                               onmouseover="this.style.color='#2980b9'; this.style.textDecoration='underline'"
                                               onmouseout="this.style.color='#3498db'; this.style.textDecoration='none'">
                                                <i class="fas fa-external-link-alt" style="margin-right: 6px; font-size: 12px;"></i>
                                                {{ $v->domain }}
                                            </a>
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle; text-align: right; border: none; font-weight: 600; font-size: 14px; color: #2c3e50;">
                                            {{ number_format($v->total_price_contract, 0, '.', '.') }}đ
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle; text-align: right; border: none; font-weight: 600; font-size: 14px; color: #27ae60;">
                                            {{ number_format($v->total_received, 0, '.', '.') }}đ
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle; text-align: right; border: none; font-weight: 700; font-size: 14px; color: #e74c3c;">
                                            {{ number_format($v->total_price_contract - $v->total_received, 0, '.', '.') }}đ
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle; border: none; font-size: 13px; {{ strtotime($v->registration_date) < time() - 90 * 24 * 60 * 60 ? 'color: #e74c3c; font-weight: 700;' : 'color: #2c3e50; font-weight: 500;' }}">
                                            @if(strtotime($v->registration_date) < time() - 90 * 24 * 60 * 60)
                                                <i class="fas fa-clock" style="margin-right: 6px; color: #e74c3c;"></i>
                                            @else
                                                <i class="far fa-calendar" style="margin-right: 6px; color: #7f8c8d;"></i>
                                            @endif
                                            {{ date('d/m', strtotime($v->registration_date)) }}
                                        </td>
                                        <td style="padding: 16px; vertical-align: middle; border: none; font-size: 13px; line-height: 1.6;">
                                            <div style="margin-bottom: 8px;">
                                                <i class="fas fa-user" style="color: #3498db; margin-right: 6px; width: 14px;"></i>
                                                <strong>{{ @$v->customer->name }}</strong> -
                                                <span style="color: #7f8c8d;">{{ @$v->customer->tel }}</span>
                                            </div>
                                            <div style="margin-bottom: 4px;">
                                                <i class="fas fa-user-tie" style="color: #9b59b6; margin-right: 6px; width: 14px;"></i>
                                                <span style="color: #2c3e50;">{{ @$v->saler->name }}</span>
                                            </div>
                                            <div style="margin-bottom: 4px;">
                                                <i class="fas fa-clipboard-list" style="color: #f39c12; margin-right: 6px; width: 14px;"></i>
                                                <span style="color: #2c3e50;">{{ @\App\Models\Admin::find($v->dh_id)->name }}</span>
                                            </div>
                                            <div>
                                                <i class="fas fa-cog" style="color: #34495e; margin-right: 6px; width: 14px;"></i>
                                                <span style="color: #2c3e50;">{{ @\App\Models\Admin::find($v->kt_id)->name }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                        <?php
                                        $tong_tien_chua_thu += $v->total_price_contract - $v->total_received;
                                        ?>
                                @endif
                            @endif
                        @endforeach
                        <tr style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
                            <td style="padding: 20px 16px; font-weight: 700; font-size: 1.25rem; border: none;">
                                <i class="fas fa-calculator" style="margin-right: 8px;"></i>TỔNG CỘNG
                            </td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; text-align: right; font-weight: 700; font-size: 1.25rem; border: none;">
                                {{ number_format($tong_tien_chua_thu, 0, '.', '.') }}đ
                            </td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
{{--                <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 8px; border-left: 4px solid #f39c12;">--}}
{{--                    <i class="fas fa-info-circle" style="color: #d35400; margin-right: 8px;"></i>--}}
{{--                    <span style="font-size: 12px; color: #d35400; font-weight: 500;">--}}
{{--                        Truy vấn các trạng thái: <strong>Khách xác nhận xong</strong>, <strong>Tạm dừng</strong>, <strong>Kết thúc</strong>--}}
{{--                    </span>--}}
{{--                </div>--}}
                <div class="note-section mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Truy vấn theo trạng thái: Khách xác nhận xong, Tạm dừng, Kết thúc
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome Icons (nếu chưa có) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* CSS bổ sung cho responsive */
    @media (max-width: 768px) {
        .custom-card {
            margin: 10px;
            padding: 15px !important;
        }

        .kt-portlet__head-title {
            font-size: 18px !important;
        }

        table {
            font-size: 12px;
        }

        th, td {
            padding: 10px 8px !important;
        }
    }

    /* Animation cho loading state */
    @keyframes shimmer {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }

    .loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
        animation: shimmer 1.5s infinite;
    }

    /* Hover effects cho toàn bộ bảng */
    .table tbody tr:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    /* Custom scrollbar */
    .kt-widget12__content::-webkit-scrollbar {
        height: 8px;
    }

    .kt-widget12__content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .kt-widget12__content::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    .kt-widget12__content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }
</style>