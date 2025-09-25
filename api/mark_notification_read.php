<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit("Not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $uid = current_user_id();

    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=:id AND user_id=:uid");
    $stmt->execute([':id'=>$id, ':uid'=>$uid]);

    echo "ok";
}
