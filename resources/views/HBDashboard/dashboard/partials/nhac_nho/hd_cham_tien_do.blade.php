<div class="custom-card p-4 mb-4" style="box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 12px; background: #fff;">
    <div class="kt-portlet__head" style="border-bottom: 2px solid #e2e8f0; padding-bottom:0.5rem; margin-bottom: 25px;">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title bold uppercase" style="color: #1e293b; font-size: 1.25rem; font-weight: 600; margin: 0; display: flex; align-items: center;">
                <i class="fas fa-exclamation-triangle" style="color: #e74c3c; margin-right: 10px; font-size: 22px;"></i>
                Dự án chậm tiến độ
            </h3>
            <?php
            $hd_nho_cham = \App\Modules\HBDashboard\Models\Bill::rightJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
                ->select(
                    'bills.id',
                    'bills.domain',
                    'bills.registration_date',
                    'bills.saler_id',
                    'bill_progress.status',
                    'bills.service_id',
                    'bill_progress.dh_id',
                    'bill_progress.kt_id',
                    'bills.total_received',
                    'bills.total_price_contract'
                )
                ->whereNotIn('bill_progress.status', ['Khách xác nhận xong', 'Tạm dừng', 'Kết thúc', 'Bỏ'])
                ->whereIn('bills.service_id', [ //  Các gói LDP, WP tiết kiệm - cơ bản phải xong trong 10 ngày
                    1,  //  ldp
                    10, //  wp tiết kiệm
                    11, //  wp cơ bản
                    17, //  ldp tiết kiệm
                    18, //  ldp cơ bản
                    19, //  ldp chuyên nghiệp
                    20, //  ldp cao cấp
                    21  //  ldp theo yêu cầu
                ])
                ->where('bills.registration_date', '<', date('Y-m-d H:i:s', time() - 20 * 24 * 60 * 60))
                ->orderBy('bills.registration_date', 'ASC')->orderBy('bill_progress.dh_id', 'ASC')->get();

            $hd_lon_cham = \App\Modules\HBDashboard\Models\Bill::rightJoin('bill_progress', 'bill_progress.bill_id', '=', 'bills.id')
                ->select(
                    'bills.id',
                    'bills.domain',
                    'bills.registration_date',
                    'bills.saler_id',
                    'bill_progress.status',
                    'bills.service_id',
                    'bill_progress.dh_id',
                    'bill_progress.kt_id',
                    'bills.total_received',
                    'bills.total_price_contract'
                )
                ->whereNotIn('bill_progress.status', ['Khách xác nhận xong', 'Tạm dừng', 'Kết thúc', 'Bỏ'])
                ->whereNotIn('bills.service_id', [ //  Các gói LDP, WP tiết kiệm - cơ bản phải xong trong 10 ngày
                    1,  //  ldp
                    10, //  wp tiết kiệm
                    11, //  wp cơ bản
                    17, //  ldp tiết kiệm
                    18, //  ldp cơ bản
                    19, //  ldp chuyên nghiệp
                    20, //  ldp cao cấp
                    21  //  ldp theo yêu cầu
                ])
                ->where('bills.registration_date', '<', date('Y-m-d H:i:s', time() - 40 * 24 * 60 * 60))
                ->orderBy('bills.registration_date', 'ASC')->orderBy('bill_progress.dh_id', 'ASC')->get();
            ?>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="kt-widget12">
            <div class="kt-widget12__content">
                <div style="padding-bottom:1.5rem; overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                    <table class="table table-striped" style="margin-bottom: 0; background: #fff; border-collapse: separate; border-spacing: 0;">
                        <thead class="kt-datatable__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <tr>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">
                                <i class="fas fa-globe" style="margin-right: 8px;"></i>Tên miền
                            </th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Dịch vụ</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Tiến độ</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Ngày ký</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: right; position: sticky; top: 0; z-index: 10;">Thanh toán</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Sale</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Điều hành</th>
                            <th style="padding: 18px 16px; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10;">Kỹ thuật</th>
                        </tr>
                        </thead>
                        <tbody class="kt-datatable__body ps ps--active-y">
                        @foreach($hd_nho_cham as $v)
                            <tr style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease; background-color: #f8f9fc;"
                                onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                                onmouseout="this.style.backgroundColor='#f8f9fc'; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                                <td style="padding: 16px; vertical-align: middle; border: none;">
                                    <a href="/admin/bill/edit/{{ $v->id }}" target="_blank"
                                       style="color: #3498db; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; transition: color 0.3s ease;"
                                       onmouseover="this.style.color='#2980b9'; this.style.textDecoration='underline'"
                                       onmouseout="this.style.color='#3498db'; this.style.textDecoration='none'">
                                        <i class="fas fa-external-link-alt" style="margin-right: 6px; font-size: 12px;"></i>
                                        {{ $v->domain }}
                                    </a>
                                </td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @$v->service->name_vi }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ $v->status }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none; font-size: 13px; {{ strtotime($v->registration_date) < time() - 30 * 24 * 60 * 60 ? 'color: #e74c3c; font-weight: 700;' : 'color: #2c3e50; font-weight: 500;' }}">
                                    @if(strtotime($v->registration_date) < time() - 30 * 24 * 60 * 60)
                                        <i class="fas fa-clock" style="margin-right: 6px; color: #e74c3c;"></i>
                                    @else
                                        <i class="far fa-calendar" style="margin-right: 6px; color: #7f8c8d;"></i>
                                    @endif
                                    {{ date('d/m', strtotime($v->registration_date)) }}
                                </td>
                                <td style="padding: 16px; vertical-align: middle; text-align: right; border: none; font-weight: 700; font-size: 14px; color: #e74c3c;">
                                    @if($v->total_received == $v->total_price_contract)
                                        Đã hết
                                    @else
                                        @if (CommonHelper::has_permission(\Auth::guard('admin')->user()->id, 'receipts_publish'))
                                            <span class="text-red">{{ round($v->total_received/1000000) }}/{{ round($v->total_price_contract/1000000) }}tr</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @$v->saler->name }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @\App\Models\Admin::find($v->dh_id)->name }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @\App\Models\Admin::find($v->kt_id)->name }}</td>
                            </tr>
                        @endforeach

                        <tr style="border-top: 3px solid; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
                            <td style="padding: 20px 16px; font-weight: 700; font-size: 1.25rem; border: none;">
                                <i class="fas fa-calculator" style="margin-right: 8px;"></i>TỔNG CỘNG
                            </td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                            <td style="padding: 20px 16px; border: none;"></td>
                        </tr>

                        @foreach($hd_lon_cham as $v)
                            <tr style="border-bottom: 1px solid #e8ecef; transition: all 0.3s ease; background-color: #fff;"
                                onmouseover="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                                onmouseout="this.style.backgroundColor='#fff'; this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                                <td style="padding: 16px; vertical-align: middle; border: none;">
                                    <a href="/admin/bill/edit/{{ $v->id }}" target="_blank"
                                       style="color: #3498db; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; transition: color 0.3s ease;"
                                       onmouseover="this.style.color='#2980b9'; this.style.textDecoration='underline'"
                                       onmouseout="this.style.color='#3498db'; this.style.textDecoration='none'">
                                        <i class="fas fa-external-link-alt" style="margin-right: 6px; font-size: 12px;"></i>
                                        {{ $v->domain }}
                                    </a>
                                </td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @$v->service->name_vi }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ $v->status }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none; font-size: 13px; {{ strtotime($v->registration_date) < time() - 60 * 24 * 60 * 60 ? 'color: #e74c3c; font-weight: 700;' : 'color: #2c3e50; font-weight: 500;' }}">
                                    @if(strtotime($v->registration_date) < time() - 60 * 24 * 60 * 60)
                                        <i class="fas fa-clock" style="margin-right: 6px; color: #e74c3c;"></i>
                                    @else
                                        <i class="far fa-calendar" style="margin-right: 6px; color: #7f8c8d;"></i>
                                    @endif
                                    {{ date('d/m', strtotime($v->registration_date)) }}
                                </td>
                                <td style="padding: 16px; vertical-align: middle; text-align: right; border: none; font-weight: 700; font-size: 14px; color: #e74c3c;">
                                    @if($v->total_received == $v->total_price_contract)
                                        Đã hết
                                    @else
                                        @if (CommonHelper::has_permission(\Auth::guard('admin')->user()->id, 'receipts_publish'))
                                            <span class="text-red">{{ round($v->total_received/1000000) }}/{{ round($v->total_price_contract/1000000) }}tr</span>
                                        @endif
                                    @endif
                                </td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @$v->saler->name }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @\App\Models\Admin::find($v->dh_id)->name }}</td>
                                <td style="padding: 16px; vertical-align: middle; border: none;">{{ @\App\Models\Admin::find($v->kt_id)->name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
{{--                    <div style="margin-top: 12px; padding: 10px 12px; background: #f9f9f9; border-radius: 6px; border-left: 3px solid #bdc3c7;">--}}
{{--                        <i class="fas fa-info-circle" style="color: #7f8c8d; margin-right: 6px; font-size: 13px;"></i>--}}
{{--                        <span style="font-size: 11px; color: #444; font-weight: 400; line-height: 1.4;">--}}
{{--                            Tổng: {{ count($hd_lon_cham) + count($hd_nho_cham) }}<br>--}}
{{--                            Truy vấn: Trạng thái != <strong>Khách xác nhận xong</strong>,--}}
{{--                            <strong>Tạm dừng</strong>, <strong>Kết thúc</strong>, <strong>Bỏ</strong>.--}}
{{--                            45 ngày chưa xong--}}
{{--                        </span>--}}
{{--                    </div>--}}
                    <div class="note-section mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
{{--                            Tổng: {{ count($hd_lon_cham) + count($hd_nho_cham) }}<br>--}}
                            Truy vấn theo trạng thái != Khách xác nhận xong, Tạm dừng, Kết thúc, Bỏ 45 ngày chưa xong
                        </small>
                    </div>

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

            th,
            td {
                padding: 10px 8px !important;
            }
        }

        /* Animation cho loading state */
        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }

            100% {
                background-position: calc(200px + 100%) 0;
            }
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