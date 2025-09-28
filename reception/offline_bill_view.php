<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/InvoiceGenerator.php';

$id = intval($_GET['id'] ?? 0);

// Ensure bill exists
$stmt = $pdo->prepare("SELECT id FROM bills WHERE id=:id");
$stmt->execute([':id'=>$id]);
if (!$stmt->fetch()) {
    die("❌ Invoice not available (bill not found).");
}

$invoice = new InvoiceGenerator($pdo);
$invoice->generate($id);
