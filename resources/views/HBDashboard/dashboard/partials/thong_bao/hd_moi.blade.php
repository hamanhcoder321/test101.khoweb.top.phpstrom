<td>
    <?php
    // Lấy ID khách từ bill hiện tại
    $id_khach = $bill->customer_id;

    // Truy vấn thẳng vào bảng users (hoặc customers) để lấy tên
    // Dùng DB::table để bỏ qua mọi rắc rối của Model
    // first() trả về object, value('name') trả về mỗi cái tên
    $ten_khach = \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $id_khach)
        ->value('name');
    ?>

    <a href="/admin/user/edit/{{ $id_khach }}" target="_blank">
        @if($ten_khach)
            {{ $ten_khach }}
        @else
            {{-- Nếu vẫn không ra tên thì in ID để đối chiếu --}}
            <span style="color:red">Không tìm thấy tên (ID: {{ $id_khach }})</span>
        @endif
    </a>
</td>