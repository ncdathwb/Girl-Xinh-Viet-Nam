<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Email configuration
$to = "ncdat.hwb@gmail.com"; // Replace with your email
$subject = "New Click Tracking Report";

// Get visitor information
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$timestamp = date('Y-m-d H:i:s');
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';

// Get all headers
$headers = getallheaders();
$acceptLanguage = isset($headers['Accept-Language']) ? $headers['Accept-Language'] : 'Unknown';
$acceptEncoding = isset($headers['Accept-Encoding']) ? $headers['Accept-Encoding'] : 'Unknown';
$connection = isset($headers['Connection']) ? $headers['Connection'] : 'Unknown';

// Detect mobile device
$isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $userAgent);
$deviceType = $isMobile ? 'Mobile' : 'Desktop';

// Detect operating system
$os = 'Unknown';
if (preg_match('/android/i', $userAgent)) {
    $os = 'Android';
} elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
    $os = 'iOS';
} elseif (preg_match('/windows/i', $userAgent)) {
    $os = 'Windows';
} elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
    $os = 'Mac';
} elseif (preg_match('/linux/i', $userAgent)) {
    $os = 'Linux';
}

// Detect browser
$browser = 'Unknown';
if (preg_match('/chrome/i', $userAgent)) {
    $browser = 'Chrome';
} elseif (preg_match('/safari/i', $userAgent)) {
    $browser = 'Safari';
} elseif (preg_match('/firefox/i', $userAgent)) {
    $browser = 'Firefox';
} elseif (preg_match('/opera|opr/i', $userAgent)) {
    $browser = 'Opera';
} elseif (preg_match('/edge/i', $userAgent)) {
    $browser = 'Edge';
} elseif (preg_match('/msie|trident/i', $userAgent)) {
    $browser = 'Internet Explorer';
}

// Get geolocation data using ipinfo.io API
$geoData = [];
$ipInfoToken = '77d2ac1a0a5ce7'; // Get free token from ipinfo.io
$geoUrl = "https://ipinfo.io/{$ip}/json?token={$ipInfoToken}";
$geoResponse = @file_get_contents($geoUrl);
if ($geoResponse) {
    $geoData = json_decode($geoResponse, true);
}

// Get screen information from JavaScript
$screenInfo = isset($_POST['screenInfo']) ? json_decode($_POST['screenInfo'], true) : [];

// Create comprehensive log entry
$logEntry = [
    'timestamp' => $timestamp,
    'ip' => $ip,
    'user_agent' => $userAgent,
    'referrer' => $referrer,
    'device_type' => $deviceType,
    'os' => $os,
    'browser' => $browser,
    'is_mobile' => $isMobile,
    'language' => $acceptLanguage,
    'encoding' => $acceptEncoding,
    'connection' => $connection,
    'geolocation' => $geoData,
    'screen_info' => $screenInfo,
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'server_name' => $_SERVER['SERVER_NAME'],
        'server_protocol' => $_SERVER['SERVER_PROTOCOL']
    ]
];

// Save to log file
$logFile = 'click_logs.txt';
file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);

// Prepare detailed email content
$emailContent = "New Click Tracking Report\n\n";
$emailContent .= "Time: " . $timestamp . "\n";
$emailContent .= "IP Address: " . $ip . "\n";
$emailContent .= "Device Type: " . $deviceType . "\n";
$emailContent .= "Operating System: " . $os . "\n";
$emailContent .= "Browser: " . $browser . "\n";
$emailContent .= "Language: " . $acceptLanguage . "\n";
$emailContent .= "Connection: " . $connection . "\n";

// Add geolocation info if available
if (!empty($geoData)) {
    $emailContent .= "\nGeolocation Information:\n";
    $emailContent .= "Country: " . ($geoData['country'] ?? 'Unknown') . "\n";
    $emailContent .= "Region: " . ($geoData['region'] ?? 'Unknown') . "\n";
    $emailContent .= "City: " . ($geoData['city'] ?? 'Unknown') . "\n";
    $emailContent .= "Location: " . ($geoData['loc'] ?? 'Unknown') . "\n";
    $emailContent .= "Organization: " . ($geoData['org'] ?? 'Unknown') . "\n";
}

// Add screen information if available
if (!empty($screenInfo)) {
    $emailContent .= "\nScreen Information:\n";
    $emailContent .= "Width: " . ($screenInfo['width'] ?? 'Unknown') . "\n";
    $emailContent .= "Height: " . ($screenInfo['height'] ?? 'Unknown') . "\n";
    $emailContent .= "Pixel Ratio: " . ($screenInfo['pixelRatio'] ?? 'Unknown') . "\n";
    $emailContent .= "Color Depth: " . ($screenInfo['colorDepth'] ?? 'Unknown') . "\n";
}

$emailContent .= "\nUser Agent: " . $userAgent . "\n";
$emailContent .= "Referrer: " . $referrer . "\n";

// Send email
mail($to, $subject, $emailContent);

// Return success response
echo json_encode(['status' => 'success']);
?> 