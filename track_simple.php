<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

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

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);
$screenInfo = isset($postData['screenInfo']) ? $postData['screenInfo'] : [];
$pageLoadTime = isset($postData['pageLoadTime']) ? round($postData['pageLoadTime'], 2) : 'Unknown';
$cookies = isset($postData['cookies']) ? $postData['cookies'] : [];

// Detect device info
$isMobile = preg_match("/(android|iphone|ipad|ipod)/i", $userAgent);
$deviceType = $isMobile ? 'Mobile' : 'Desktop';

// Detect operating system
$os = 'Unknown';
$osVersion = 'Unknown';
if (preg_match('/windows/i', $userAgent)) {
    $os = 'Windows';
    if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
        $osVersion = $matches[1];
    }
} elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
    $os = 'Mac OS X';
    if (preg_match('/Mac OS X ([0-9._]+)/', $userAgent, $matches)) {
        $osVersion = str_replace('_', '.', $matches[1]);
    }
} elseif (preg_match('/android/i', $userAgent)) {
    $os = 'Android';
    if (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
        $osVersion = $matches[1];
    }
} elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
    $os = 'iOS';
    if (preg_match('/OS ([0-9._]+)/', $userAgent, $matches)) {
        $osVersion = str_replace('_', '.', $matches[1]);
    }
} elseif (preg_match('/linux/i', $userAgent)) {
    $os = 'Linux';
}

// Detect browser details
$browser = 'Unknown';
$browserVersion = 'Unknown';
if (preg_match('/chrome/i', $userAgent)) {
    $browser = 'Chrome';
    preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches);
    $browserVersion = $matches[1] ?? 'Unknown';
} elseif (preg_match('/firefox/i', $userAgent)) {
    $browser = 'Firefox';
    preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches);
    $browserVersion = $matches[1] ?? 'Unknown';
} elseif (preg_match('/safari/i', $userAgent)) {
    $browser = 'Safari';
    preg_match('/Version\/([0-9.]+)/', $userAgent, $matches);
    $browserVersion = $matches[1] ?? 'Unknown';
} elseif (preg_match('/edge/i', $userAgent)) {
    $browser = 'Edge';
    preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches);
    $browserVersion = $matches[1] ?? 'Unknown';
} elseif (preg_match('/opera|opr/i', $userAgent)) {
    $browser = 'Opera';
    preg_match('/(?:Opera|OPR)\/([0-9.]+)/', $userAgent, $matches);
    $browserVersion = $matches[1] ?? 'Unknown';
}

// Get location information using ipinfo.io
$locationInfo = [];
$ipInfoToken = '77d2ac1a0a5ce7';
$geoUrl = "https://ipinfo.io/{$ip}/json?token={$ipInfoToken}";
$geoResponse = @file_get_contents($geoUrl);
if ($geoResponse) {
    $locationInfo = json_decode($geoResponse, true);
}

// Create detailed log message
$logMessage = "=== Click Log ===\n";
$logMessage .= "Time: $timestamp\n";
$logMessage .= "IP: $ip\n";
$logMessage .= "Device: $deviceType\n";

// Page Load Information
$logMessage .= "\n--- Page Load Info ---\n";
$logMessage .= "Load Time: {$pageLoadTime}ms\n";

// Cookies Information
$logMessage .= "\n--- Cookies Info ---\n";
if (!empty($cookies)) {
    foreach ($cookies as $name => $value) {
        $logMessage .= "$name: $value\n";
    }
} else {
    $logMessage .= "No cookies found\n";
}

// Operating System Information
$logMessage .= "\n--- OS Info ---\n";
$logMessage .= "OS: $os\n";
$logMessage .= "OS Version: $osVersion\n";

// Browser Information
$logMessage .= "\n--- Browser Info ---\n";
$logMessage .= "Browser: $browser\n";
$logMessage .= "Version: $browserVersion\n";
$logMessage .= "User Agent: $userAgent\n";

// Language and Network Info
$logMessage .= "\n--- Language & Network ---\n";
$logMessage .= "Language: $acceptLanguage\n";
$logMessage .= "Encoding: $acceptEncoding\n";
$logMessage .= "Connection: $connection\n";

// Screen Information
$logMessage .= "\n--- Screen Info ---\n";
if (!empty($screenInfo)) {
    $logMessage .= "Width: " . ($screenInfo['width'] ?? 'Unknown') . "px\n";
    $logMessage .= "Height: " . ($screenInfo['height'] ?? 'Unknown') . "px\n";
    $logMessage .= "Pixel Ratio: " . ($screenInfo['pixelRatio'] ?? 'Unknown') . "\n";
    $logMessage .= "Color Depth: " . ($screenInfo['colorDepth'] ?? 'Unknown') . " bits\n";
} else {
    $logMessage .= "Screen info not available\n";
}

// Location Information
$logMessage .= "\n--- Location Info ---\n";
if (!empty($locationInfo)) {
    $logMessage .= "Country: " . ($locationInfo['country'] ?? 'Unknown') . "\n";
    $logMessage .= "Region: " . ($locationInfo['region'] ?? 'Unknown') . "\n";
    $logMessage .= "City: " . ($locationInfo['city'] ?? 'Unknown') . "\n";
    $logMessage .= "Location: " . ($locationInfo['loc'] ?? 'Unknown') . "\n";
    $logMessage .= "Organization: " . ($locationInfo['org'] ?? 'Unknown') . "\n";
} else {
    $logMessage .= "Location info not available\n";
}

$logMessage .= "\nFrom: $referrer\n";
$logMessage .= "================\n\n";

// Save to log file
$logFile = 'clicks.txt';
file_put_contents($logFile, $logMessage, FILE_APPEND);

// Return success
echo json_encode(['status' => 'success']);
?> 