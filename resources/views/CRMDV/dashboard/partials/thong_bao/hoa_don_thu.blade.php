@if(!isset($_GET['ajax-load']))
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/admin/dashboard/ajax/load-khoi?ajax-load=true&file=CRMDV.dashboard.partials.thong_bao.hoa_don_thu',
                type: 'GET',
                data: {

                },
                success: function (html) {
                    $('#hoa_don_thu').html(html);
                },
                error: function () {
                    console.log('lỗi load khối CRMDV/partials/thong_bao/hoa_don_thu');
                }
            });
        });
    </script>
    <div class="kt-portlet kt-portlet--height-fluid">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title bold uppercase">
                    Tiền về tk công ty
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body">
            <div class="kt-widget12">
                <div class="kt-widget12__content" id="hoa_don_thu">
                    <img class="tooltip_info_loading"
                         src="/images_core/icons/loading.gif">
                </div>
            </div>
        </div>
    </div>
@else
        <?php

        $so_tien_thang_nay = \App\CRMDV\Models\BillReceipts::whereRaw("date >= '" . date('Y-m-01 00:00:00', strtotime('-0 months')) . "'")  //  lấy tháng này
            ->where('price', '>', 0)
            ->where('receiving_account', 'ACB 2288668668')
            ->sum('price');
        $so_tien_thang_truoc = \App\CRMDV\Models\BillReceipts::whereRaw("date >= '" . date('Y-m-01 00:00:00', strtotime('-1 months')) . "'")
            ->whereRaw("date <= '" . date('Y-m-01 00:00:00', strtotime('-0 months')) . "'")
            ->where('price', '>', 0)
            ->where('receiving_account', 'ACB 2288668668')
            ->sum('price');
        $so_tien_thang_truoc_nua = \App\CRMDV\Models\BillReceipts::whereRaw("date >= '" . date('Y-m-01 00:00:00', strtotime('-2 months')) . "'")
            ->whereRaw("date <= '" . date('Y-m-01 00:00:00', strtotime('-1 months')) . "'")
            ->where('price', '>', 0)
            ->where('receiving_account', 'ACB 2288668668')
            ->sum('price');
        ?>
    <table class="table table-striped">
        <thead class="kt-datatable__head">
        <tr>
            <th>Tháng</th>
            <th>Tổng tiền thu</th>
        </tr>
        </thead>
        <tbody class="kt-datatable__body ps ps--active-y">
            <tr>
                <td>{{ date('m', strtotime('-0 months')) }}</td>
                <td>{{ number_format($so_tien_thang_nay, 0, '.', '.') }}đ</td>
            </tr>
            <tr>
                <td>{{ date('m', strtotime('-1 months')) }}</td>
                <td>{{ number_format($so_tien_thang_truoc, 0, '.', '.') }}đ</td>
            </tr>
            <tr>
                <td>{{ date('m', strtotime('-2 months')) }}</td>
                <td>{{ number_format($so_tien_thang_truoc_nua, 0, '.', '.') }}đ</td>
            </tr>
        </tbody>
    </table>
@endif
