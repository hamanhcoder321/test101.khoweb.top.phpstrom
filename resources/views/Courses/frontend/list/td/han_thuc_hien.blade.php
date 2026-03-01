<?php
$ngay_tao = Carbon\Carbon::parse($admin->created_at);
$so_ngay = $item->ngay_thuc_hien;
$ngay_ket_thuc = $ngay_tao->addDays($so_ngay);
?>

{{$ngay_ket_thuc->format('d-m-Y')}}