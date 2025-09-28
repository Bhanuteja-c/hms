<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$bill_id = intval($_GET['id'] ?? 0);

// Security: Check bill ownership
$stmt = $pdo->prepare("
    SELECT b.*, u.name AS doctor_name 
    FROM bills b
    JOIN users u ON b.doctor_id = u.id
    WHERE b.id=:id AND b.patient_id=:pid
    LIMIT 1
");
$stmt->execute([':id' => $bill_id, ':pid' => $pid]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    http_response_code(404);
    die("❌ Bill not found or access denied.");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Bill #<?= e($bill['id']) ?> - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    iframe { 
      width: 100%; 
      height: 80vh; 
      border: 1px solid #e5e7eb; 
      border-radius: 0.5rem; 
      background: #fff;
    }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <a href="bills.php" 
           class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 flex items-center gap-1 text-sm">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to My Bills
        </a>
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="file-text" class="w-6 h-6 text-indigo-600"></i>
          Bill #<?= e($bill['id']) ?>
        </h2>
      </div>
      <span class="px-3 py-1 rounded-full text-sm
        <?= $bill['status']==='paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= ucfirst($bill['status']) ?>
      </span>
    </div>

    <!-- Bill Info -->
    <div class="bg-white p-4 rounded shadow mb-4">
      <p><strong>Doctor:</strong> <?= e($bill['doctor_name']) ?></p>
      <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($bill['created_at'])) ?></p>
      <?php if ($bill['status'] === 'paid' && !empty($bill['paid_at'])): ?>
        <p><strong>Paid On:</strong> <?= date('d M Y H:i', strtotime($bill['paid_at'])) ?></p>
      <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 mb-4">
      <a href="bill_pdf.php?id=<?= e($bill['id']) ?>" target="_blank"
         class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
        <i data-lucide="download" class="w-4 h-4"></i> Download PDF
      </a>
      <button onclick="printPDF()" 
              class="px-4 py-2 bg-green-600 text-white rounded flex items-center gap-2 hover:bg-green-700">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>

    <!-- PDF Preview -->
    <iframe id="pdfFrame" src="bill_pdf.php?id=<?= e($bill['id']) ?>"></iframe>
  </main>

  <script>
    lucide.createIcons();
    function printPDF() {
      const iframe = document.getElementById('pdfFrame');
      if (iframe && iframe.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      }
    }
  </script>
</body>
</html>
