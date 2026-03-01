<?php
$url = "https://support.pavietnam.vn/quicklink.php?ticket=5295040&cus=624474&key=2425377991370&pin=529504HFX0";

// Khởi tạo cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

// Kiểm tra lỗi
if ($response === false) {
    echo "Không thể lấy nội dung từ URL: $url";
    exit;
}

// Trả về nội dung của trang
$response = str_replace('P.A','HBWEB', $response);
$response = str_replace('https://support.pavietnam.vn','https://hbweb.vn', $response);
$response = str_replace('action="','action="https://support.pavietnam.vn/', $response);
echo $response;
?>
