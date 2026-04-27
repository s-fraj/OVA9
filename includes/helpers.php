<?php

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function logAction($pdo, $user_id, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, ip) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $ip]);
}

function generateFakeScan($url) {
    $risks = ['low', 'medium', 'high'];
    $risk = $risks[array_rand($risks)];

    return [
        "result" => "Scan completed for $url",
        "risk" => $risk,
        "report" => "
        <html>
        <h1>OVA9 Security Report</h1>
        <p>URL: $url</p>
        <p>Risk Level: $risk</p>
        <p>Status: Completed</p>
        </html>
        "
    ];
}
