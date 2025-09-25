<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['ok'=>false, 'msg'=>'Not logged in']);
    exit;
}

$uid = current_user_id();
$role = current_user_role();

// Unread count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
$stmt->execute([':uid'=>$uid]);
$unread = $stmt->fetchColumn();

// Latest 5
$stmt = $pdo->prepare("SELECT message, link, DATE_FORMAT(created_at,'%d %b %Y %H:%i') as created_at 
                       FROM notifications WHERE user_id=:uid ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':uid'=>$uid]);
$latest = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true, 'unread'=>$unread, 'latest'=>$latest, 'role'=>$role]);
