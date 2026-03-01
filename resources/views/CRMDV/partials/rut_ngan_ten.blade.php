<?php
if (!function_exists('formatName')) {
    if (!function_exists('formatName')) {
        function formatName($str) {
            // Tách chuỗi thành các từ
            $words = explode(' ', $str);

            // Đếm số lượng từ
            $wordCount = count($words);

            // Tạo chuỗi kết quả
            $result = '';
            foreach ($words as $index => $word) {
                if ($index < $wordCount - 1) {
                    // Nếu không phải từ cuối cùng
                    $result .= mb_strtolower(mb_substr($word, 0, 1)) . '.'; // Chữ thường với dấu chấm
                } else {
                    // Từ cuối cùng, viết hoa chữ cái đầu
                    $result .= ucfirst($word);
                }
            }

            return $result;
        }
    }

}
//
//// Ví dụ sử dụng
//echo formatName("Nguyễn Quang Huy"); // Kết quả: N.Q Huy
//echo "\n";
//echo formatName("Hoàng Hiệu");       // Kết quả: H Hiệu
?>
