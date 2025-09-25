<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$bill_id = intval($_GET['id'] ?? 0);

// Security: Check bill ownership
$stmt = $pdo->prepare("SELECT * FROM bills WHERE id=:id AND patient_id=:pid");
$stmt->execute([':id'=>$bill_id, ':pid'=>$pid]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("Bill not found or access denied.");
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bill #<?=e($bill['id'])?> - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    iframe { width: 100%; height: 80vh; border: 1px solid #e5e7eb; border-radius: 0.5rem; }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="file-text" class="w-6 h-6 text-indigo-600"></i>
      Bill #<?=e($bill['id'])?>
    </h2>

    <div class="flex gap-3 mb-4">
      <a href="bill_pdf.php?id=<?=e($bill['id'])?>" target="_blank"
         class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2">
        <i data-lucide="download" class="w-4 h-4"></i> Download PDF
      </a>
      <button onclick="printPDF()" 
              class="px-4 py-2 bg-green-600 text-white rounded flex items-center gap-2">
        <i data-lucide="printer" class="w-4 h-4"></i> Print
      </button>
    </div>

    <iframe id="pdfFrame" src="bill_pdf.php?id=<?=e($bill['id'])?>"></iframe>
  </main>

  <script>
    lucide.createIcons();

    function printPDF() {
      const iframe = document.getElementById('pdfFrame');
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
    }
  </script>
</body>
</html>
