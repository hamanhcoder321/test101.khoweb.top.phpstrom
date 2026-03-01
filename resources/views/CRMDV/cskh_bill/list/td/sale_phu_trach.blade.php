{{--<a href="/admin/{{ $module['code'] }}/edit/{{ $item->id }}"--}}
{{--   style="    font-size: 14px!important;" class="">--}}
{{--    Xem--}}
{{-- </a>--}}
{{-- |--}}
{{-- <a href="/admin/{{ $module['code'] }}/edit/{{ $item->id }}/bo-cham-soc-lan-nay"--}}
{{--   style="    font-size: 14px!important;" class="">--}}
{{--    Bỏ qua--}}
{{-- </a>--}}

<span style="width: 100px;" class="">
        <span class="kt-font-bold">
            <?php
            $nv_phu_trach = \App\Models\Admin::select(['id', 'name'])->whereIn('id', explode('|', $item->staff_care))->get();
            ?>
            @foreach($nv_phu_trach as $nv)
                {{ $nv->name }}<br>
            @endforeach
        </span>
</span>
