<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Kiểm tra hệ thống Tracking</h2>";

// 1. Kiểm tra quyền ghi file
echo "<h3>1. Kiểm tra quyền ghi file:</h3>";
$testFile = 'test_write.txt';
if (file_put_contents($testFile, 'Test write: ' . date('Y-m-d H:i:s'))) {
    echo "✅ Có thể ghi file<br>";
    unlink($testFile); // Xóa file test
} else {
    echo "❌ Không thể ghi file<br>";
}

// 2. Kiểm tra hàm mail()
echo "<h3>2. Kiểm tra chức năng gửi mail:</h3>";
$testMail = mail('ncdat.hwb@gmail.com', 'Test Mail', 'Test content');
echo $testMail ? "✅ Có thể gửi mail<br>" : "❌ Không thể gửi mail<br>";

// 3. Kiểm tra PHP version và extensions
echo "<h3>3. Kiểm tra PHP và Extensions:</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "JSON Extension: " . (extension_loaded('json') ? "✅" : "❌") . "<br>";
echo "cURL Extension: " . (extension_loaded('curl') ? "✅" : "❌") . "<br>";

// 4. Kiểm tra kết nối ipinfo.io
echo "<h3>4. Kiểm tra kết nối ipinfo.io:</h3>";
$ip = $_SERVER['REMOTE_ADDR'];
$ipInfoToken = '77d2ac1a0a5ce7';
$geoUrl = "https://ipinfo.io/{$ip}/json?token={$ipInfoToken}";
$geoResponse = @file_get_contents($geoUrl);
if ($geoResponse) {
    echo "✅ Kết nối ipinfo.io thành công<br>";
    $geoData = json_decode($geoResponse, true);
    echo "IP: " . ($geoData['ip'] ?? 'Unknown') . "<br>";
    echo "Country: " . ($geoData['country'] ?? 'Unknown') . "<br>";
} else {
    echo "❌ Không thể kết nối ipinfo.io<br>";
}

// 5. Kiểm tra các biến môi trường
echo "<h3>5. Kiểm tra biến môi trường:</h3>";
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "<br>";
echo "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "<br>";
echo "SERVER_SOFTWARE: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// 6. Kiểm tra file click_logs.txt
echo "<h3>6. Kiểm tra file click_logs.txt:</h3>";
$logFile = 'click_logs.txt';
if (file_exists($logFile)) {
    echo "✅ File click_logs.txt tồn tại<br>";
    echo "Kích thước: " . filesize($logFile) . " bytes<br>";
    if (is_writable($logFile)) {
        echo "✅ Có quyền ghi file<br>";
    } else {
        echo "❌ Không có quyền ghi file<br>";
    }
} else {
    echo "❌ File click_logs.txt chưa tồn tại<br>";
    // Thử tạo file
    if (file_put_contents($logFile, '')) {
        echo "✅ Đã tạo file click_logs.txt<br>";
    } else {
        echo "❌ Không thể tạo file click_logs.txt<br>";
    }
}

// 7. Kiểm tra CORS headers
echo "<h3>7. Kiểm tra CORS headers:</h3>";
$headers = getallheaders();
echo "Access-Control-Allow-Origin: " . (isset($headers['Access-Control-Allow-Origin']) ? "✅" : "❌") . "<br>";
echo "Access-Control-Allow-Methods: " . (isset($headers['Access-Control-Allow-Methods']) ? "✅" : "❌") . "<br>";

// 8. Kiểm tra POST data
echo "<h3>8. Kiểm tra POST data:</h3>";
$rawData = file_get_contents('php://input');
echo "Raw POST data: " . ($rawData ? $rawData : "Không có data") . "<br>";

// 9. Kiểm tra thư mục hiện tại
echo "<h3>9. Kiểm tra thư mục hiện tại:</h3>";
echo "Current directory: " . getcwd() . "<br>";
echo "Directory permissions: " . substr(sprintf('%o', fileperms(getcwd())), -4) . "<br>";

// 10. Kiểm tra memory limit
echo "<h3>10. Kiểm tra memory limit:</h3>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . " seconds<br>";

// Thêm nút test tracking
echo "<h3>11. Test Tracking:</h3>";
echo "<button onclick='testTracking()'>Test Tracking</button>";
echo "<div id='testResult'></div>";

// JavaScript để test tracking
echo "<script>
function testTracking() {
    const screenInfo = {
        width: window.screen.width,
        height: window.screen.height,
        pixelRatio: window.devicePixelRatio,
        colorDepth: window.screen.colorDepth
    };

    fetch('track.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            screenInfo: screenInfo
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('testResult').innerHTML = '✅ Tracking test thành công: ' + JSON.stringify(data);
    })
    .catch(error => {
        document.getElementById('testResult').innerHTML = '❌ Tracking test thất bại: ' + error;
    });
}
</script>";
?> 