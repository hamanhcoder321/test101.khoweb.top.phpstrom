<?php
function decryptDataCBC($data, $keyHex, $iv) {
    $ciphertext = base64_decode($data);
    $key = pack("H*", $keyHex);

    $plaintext = openssl_decrypt(
        $ciphertext,
        "AES-128-CBC",
        $key,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $iv
    );

    if ($plaintext && strpos($plaintext, "{") !== false) {
        return rtrim($plaintext, "\0");
    }
    return false;
}

$data = "1jS3CuoTdWIxqcdP33Glv6ToLBnbOjn/JjGSShsv6/jYIZ9rOo3loPKvsv3jyjlaebcPpImcKE5W5Sd1gKBTdqLMqZoM1FrHcXsNMBWUZtFKn108aKGjh6oH5XP6rH13pv35K3yonzqTjb6eI9KKpb90o1lfMF1qJC9gbnnPRQcpMLimd++M5OPL0qD76Lad";
$key  = "ff39fc173e7ed3c35e01d139e6042e64";

// Một số IV hay gặp
$ivs = [
    str_repeat("\0", 16),               // 16 byte toàn 0
    substr(pack("H*", $key), 0, 16),    // lấy từ key
    "1234567890abcdef",                 // chuỗi thường gặp
];

foreach ($ivs as $iv) {
    $result = decryptDataCBC($data, $key, $iv);
    if ($result !== false) {
        echo "✅ IV: " . bin2hex($iv) . "\n";
        echo $result . "\n";
    } else {
        echo "❌ IV thử: " . bin2hex($iv) . " thất bại\n";
    }
}
