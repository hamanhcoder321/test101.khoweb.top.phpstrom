@if($item->{$field['name']} != null)
    @php
        $date1 = date_create(@$item->dating);
        $date2 = date_create(date('Y-m-d'));
        $diff = date_diff($date1, $date2);
    @endphp

    <div class="table-date-cell">
        <input type="date"
               name="dating_change"
               class="td-field-{{ $item->id }}"
               value="{{ date('Y-m-d', strtotime($item->dating)) }}"
               readonly>

        @if(strtotime(date('Y-m-d')) > strtotime($item->{$field['name']}))
            <span class="status overdue">Trễ {{ $diff->format('%a') }} ngày</span>
        @elseif(strtotime(date('Y-m-d')) == strtotime($item->{$field['name']}))
            <span class="status today">Đến ngày TT</span>
        @else
            <span class="status future">{{ date('d/m/Y', strtotime($item->{$field['name']})) }}</span>
        @endif
    </div>
@endif

<style>
    .table-date-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        padding: 4px 0;
        font-family: 'Poppins', Helvetica, sans-serif;
    }

    .table-date-cell input[type="date"] {
        width: 145px;
        font-size: 16px;
        padding: 7px 10px;
        border: none;
        border-radius: 6px;
        background: transparent;
        color: #222;
        cursor: pointer;
        transition: all 0.25s ease;
        font-family: 'Poppins', Helvetica, sans-serif;
    }

    .table-date-cell input[type="date"]:hover {
        background-color: #f3f3f3;
    }

    .table-date-cell input[type="date"]:focus {
        border: 1px solid #bbb;
        background: #fff;
        cursor: text;
        outline: none;
    }


    .table-date-cell .status.overdue {
        color: #e63946;
        font-weight: 600;
    }

    .table-date-cell .status.today {
        color: #2a9d8f;
        font-weight: 600;
    }

    .table-date-cell .status.future {
        color: #555;
        font-style: italic;
    }
</style>



<script>
    $(document).ready(function() {
        const field = $('.td-field-{{ $item->id }}');
        let originalValue = field.val(); // Lưu giá trị ban đầu

        // Khi click vào thì cho phép sửa
        field.on('click', function() {
            $(this).removeAttr('readonly').focus();
        });

        // Khi rời ô hoặc thay đổi giá trị
        field.on('blur change', function() {
            const newValue = $(this).val();
            $(this).attr('readonly', true);

            // Nếu không thay đổi thì không gửi AJAX
            if (newValue === originalValue) {
                return;
            }

            // Cập nhật lại giá trị gốc sau khi gửi
            originalValue = newValue;

            $.ajax({
                url: '/admin/lead/ajax-update',
                type: 'POST',
                data: {
                    data: { dating: newValue },
                    id: '{{ $item->id }}'
                },
                success: function() {
                    location.reload();
                },
                error: function() {
                    console.log('Có lỗi xảy ra, vui lòng load lại trang và thử lại!');
                }
            });
        });
    });
</script>
