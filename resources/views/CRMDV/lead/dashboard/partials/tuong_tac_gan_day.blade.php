@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CRMDV.lead.dashboard.partials.tuong_tac_gan_day',
                type: 'GET',
                data: {

                },
                success: function (html) {
                    $('#tuong_tac_gan_day').html(html);
                },
                error: function () {
                    console.log('lỗi load khối partials/xep_hang/tuong_tac_gan_day');
                }
            });
        });
    </script>
    <div class="kt-portlet kt-portlet--height-fluid">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title bold uppercase">
                    Tương tác gần đây
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body">
            <div class="kt-widget12">
                <div class="kt-widget12__content" id="tuong_tac_gan_day">
                    <img class="tooltip_info_loading"
                         src="/images_core/icons/loading.gif">

                </div>
            </div>
        </div>
    </div>
@else

    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table-striped tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Sale</th>
            <th>Khách</th>
            <th>Tương tác</th>
            <th>Thời gian</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Hoàng Hiệu</td>
            <td>Thắm</td>
            <td>: Không nghe</td>
            <td>11:33 26/05</td>
        </tr>
        <tr>
            <td>Hoàng Hiệu</td>
            <td>Phùng Hùng</td>
            <td>: gọi lại sau nhé.</td>
            <td>11:33 26/05</td>
        </tr>
        <!-- Các dòng khác của bảng -->
        </tbody>
    </table>

    <table class="table table-striped">
        <thead class="kt-datatable__head">
        <tr>
            <th>Sale</th>
            <th>Khách</th>
            <th>Tương tác</th>
            <th>Thời gian</th>
        </tr>
        </thead>
        <tbody class="kt-datatable__body ps ps--active-y">

            <?php
            $room_ids = [
                '' => 'Khác',
                1 => 'Phòng kinh doanh 1',
                2 => 'Phòng kinh doanh 2',
                3 => 'Phòng kinh doanh 3',
                4 => 'Phòng kinh doanh 4',
                5 => 'Phòng kinh doanh 5',
                6 => 'Phòng Telesale',
                20 => 'Marketing',
            ];
            ?>
            <?php
                $lead_contacted_log = \App\CRMDV\Models\LeadContactedLog::where('created_at', '>=', date('Y-m-d 00:00:00', strtotime('-2 days')))
                    ->where('type', 'lead')
                    ->orderBy('id', 'desc')
                    ->get();
                $thong_ke = [];
            ?>
        @foreach($lead_contacted_log as $v)
            <?php
                if(!isset($thong_ke[$v->admin_id][date('d', strtotime($v->created_at))])) {
                    $thong_ke[$v->admin_id][date('d', strtotime($v->created_at))] = 1;
                } else {
                    $thong_ke[$v->admin_id][date('d', strtotime($v->created_at))] ++;
                }
                ?>
            <tr>
                <td>
                    {{ @$v->admin->name }}
                </td>
                <td>{{ @$v->lead->name }}</td>
                <td>{{ @$v->title }}: {{ @$v->note }}</td>
                <td>{{ date('H:i d/m', strtotime($v->created_at)) }}</td>
            </tr>
        @endforeach



        </tbody>
    </table>

    <h4>Thống kê số lượng tương tác theo ngày</h4>
    <table>
        <thead>
        <tr>
            <th>Sale</th>
            <th>Ngày {{ date('d', strtotime('-2 days')) }}</th>
            <th>Ngày {{ date('d', strtotime('-1 day')) }}</th>
            <th>Ngày {{ date('d') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($thong_ke as $admin_id => $v)
            <tr>
                <td>
                    {{ @\App\Models\Admin::find($admin_id)->name }}
                </td>
                <td>{{ @$v[date('d', strtotime('-2 days'))] }}</td>
                <td>{{ @$v[date('d', strtotime('-1 day'))] }}</td>
                <td>{{ @$v[date('d')] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif