<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$bill_id = intval($_GET['bill_id'] ?? 0);
if (!$bill_id) { die("Bill required."); }

$stmt = $pdo->prepare("SELECT * FROM bills WHERE id=:id AND patient_id=:pid LIMIT 1");
$stmt->execute([':id'=>$bill_id, ':pid'=>current_user_id()]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$bill) { die("Bill not found."); }

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $card = trim($_POST['card'] ?? '');
        $amount = floatval($bill['total_amount']);

        if (!$card || strlen($card) < 12) {
            $errors[] = "Invalid card number.";
        }

        if (empty($errors)) {
            $ok = rand(0,9) > 1; // ~80% success
            if ($ok) {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE bills SET status='paid', paid_at=NOW() WHERE id=:id")
                    ->execute([':id'=>$bill_id]);
                $pdo->prepare("INSERT INTO payments (bill_id,amount,method,transaction_id) 
                               VALUES (:bid,:amt,'card',:tx)")
                    ->execute([
                        ':bid'=>$bill_id,
                        ':amt'=>$amount,
                        ':tx'=>bin2hex(random_bytes(6))
                    ]);
                audit_log($pdo, current_user_id(), 'payment_success', json_encode(['bill_id'=>$bill_id,'amount'=>$amount]));

                // ✅ Notify patient
                $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")
                    ->execute([
                        ':uid'=>current_user_id(),
                        ':msg'=>"Your payment of $".number_format($amount,2)." was successful.",
                        ':link'=>"/healsync/patient/bills.php"
                    ]);

                $pdo->commit();
                $success = "Payment successful. Receipt generated.";
            } else {
                audit_log($pdo, current_user_id(), 'payment_failed', json_encode(['bill_id'=>$bill_id]));
                $errors[] = "Payment failed (simulated). Please try again.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pay Bill - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="credit-card" class="w-6 h-6 text-green-600"></i>
      Pay Bill #<?= e($bill['id']) ?>
    </h2>

    <div class="bg-white p-4 rounded shadow mb-4">
      <p>Amount: <strong>$<?= number_format($bill['total_amount'],2) ?></strong></p>
      <p>Status: <?= e($bill['status']) ?></p>
    </div>

    <?php if ($errors): ?>
      <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
        <?= implode('<br>', $errors) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <?php if ($bill['status'] !== 'paid'): ?>
    <form method="post" class="bg-white p-6 rounded shadow space-y-4">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">

      <div>
        <label class="block mb-1 font-medium">Card number (dummy)</label>
        <input name="card" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Name on card</label>
        <input name="cname" class="w-full border rounded p-2" required>
      </div>

      <button class="px-4 py-2 bg-green-600 text-white rounded flex items-center gap-2 hover:bg-green-700">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        Pay $<?= number_format($bill['total_amount'],2) ?>
      </button>
    </form>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
