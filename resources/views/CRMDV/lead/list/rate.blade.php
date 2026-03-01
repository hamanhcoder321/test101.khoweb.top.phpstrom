@php
    $rateValue = $item->{$field['name']} ?? '';
    $tagName  = '';
    $tagColor = '';

    if (!empty($rateValue)) {
        if (is_numeric($rateValue)) {
            // Lưu bằng ID tag (mới) → truy vấn trực tiếp bảng tags
            $tag = \DB::table('tags')->where('id', (int)$rateValue)->first();
        } else {
            // Dữ liệu cũ lưu dạng chuỗi → tìm theo name + type
            $tag = \DB::table('tags')
                        ->where('name', $rateValue)
                        ->where('type', 'lead_rate')
                        ->first();
        }
        if ($tag) {
            $tagName  = $tag->name;
            $tagColor = !empty($tag->color) ? $tag->color : '#6c757d';
        }
    }
@endphp

@if(!empty($tagName))
    <span style="
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        background-color: {{ $tagColor }};
        color: #fff;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    ">{{ $tagName }}</span>
@endif