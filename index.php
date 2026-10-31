<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo json_encode([
        "error" => true,
        "message" => "Folder vendor belum ada. Jalankan composer install terlebih dahulu."
    ], JSON_PRETTY_PRINT);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use DeviceDetector\DeviceDetector;

$userAgent = isset($_GET['ua']) ? trim($_GET['ua']) : '';
if (empty($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
}

if (empty($userAgent)) {
    echo json_encode([
        "error" => true,
        "message" => "Parameter 'ua' tidak ditemukan."
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    $dd = new DeviceDetector($userAgent);
    $dd->parse();

    $detectedModel = $dd->getModel();
    $brandName = $dd->getBrandName();
    $brandStr = is_array($brandName) ? ($brandName['name'] ?? '') : $brandName;

    $brandText = $brandStr ?: "Device";
    $modelText = $detectedModel ?: "Smartphone";
    $deviceImageUrl = "https://ui-avatars.com/api/?name=" . urlencode($brandText . " " . $modelText) . "&size=512&background=0D1117&color=00E5FF&bold=true&format=png&rounded=true";

    $result = [
        "isBot" => $dd->isBot(),
        "clientInfo" => $dd->getClient(),
        "browserFamily" => null,
        "isMobileOnlyBrowser" => false,
        "osInfo" => $dd->getOs(),
        "osFamily" => $dd->getOs()['family'] ?? null,
        "device" => $dd->getDevice(),
        "deviceName" => $dd->getDeviceName(),
        "deviceBrand" => $brandName,
        "model" => $detectedModel,
        "image_url" => $deviceImageUrl,
        "icons" => [
            "browser" => null,
            "os" => "/icons/os/" . ($dd->getOs()['short_name'] ?? '') . ".png",
            "device" => "/icons/devices/" . $dd->getDeviceName() . ".png",
            "brand" => "/icons/brand/" . $brandStr . ".png"
        ],
        "userAgent" => $userAgent
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>