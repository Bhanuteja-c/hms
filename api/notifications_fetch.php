<?php
// api/notifications_fetch.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error'=>'unauthenticated']);
    exit;
}

$uid = current_user_id();

// fetch latest (limit 20)
$stmt = $pdo->prepare("SELECT id, message, link, is_read, created_at FROM notifications WHERE user_id=:uid ORDER BY created_at DESC LIMIT 50");
$stmt->execute([':uid'=>$uid]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// unread count
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id=:uid AND is_read=0");
$stmt->execute([':uid'=>$uid]);
$unread = (int)$stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'notifications' => $notes,
    'unread_count' => $unread
], JSON_UNESCAPED_SLASHES);
