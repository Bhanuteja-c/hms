<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!is_logged_in()) {
    http_response_code(403);
    exit("Not logged in");
}

$uid = current_user_id();
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=:uid")->execute([':uid'=>$uid]);
echo "ok";
