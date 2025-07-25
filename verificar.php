<?php
header("Content-Type: application/json");

function getClientIP() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}

function isVPN($ip) {
    // Puedes usar IPHub.io, ipapi.co o ipdata.co
    $apiKey = 'TU_API_KEY'; // Regístrate y consigue una clave
    $url = "https://ipapi.co/{$ip}/json/";

    $data = @file_get_contents($url);
    if (!$data) return true;

    $json = json_decode($data, true);
    return isset($json['security']) && ($json['security']['vpn'] ?? false);
}

function recentlyAccessed($fingerprint) {
    $file = "logs.json";
    $log = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $now = time();

    if (isset($log[$fingerprint]) && $now - $log[$fingerprint] < 300) {
        return true;
    }

    $log[$fingerprint] = $now;
    file_put_contents($file, json_encode($log));
    return false;
}

// Leer cuerpo JSON
$input = json_decode(file_get_contents("php://input"), true);
$ip = getClientIP();
$fingerprint = $input["fingerprint"] ?? '';
$score = $input["score"] ?? 0;
$mobile = $input["mobile"] ?? false;
$devtools = $input["devtools"] ?? true;
$headless = $input["headless"] ?? true;

$vpn = isVPN($ip);
$repeat = recentlyAccessed($fingerprint);

$response = [
  "success" => false
];

if ($score >= 2 && $mobile && !$devtools && !$headless && !$vpn && !$repeat) {
  $response["success"] = true;
}

echo json_encode($response);
