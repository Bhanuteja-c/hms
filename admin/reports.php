<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';




// Appointments by status
$apptData = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

// Revenue breakdown
$paid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='paid'")->fetchColumn() ?: 0;
$unpaid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='unpaid'")->fetchColumn() ?: 0;

// Monthly revenue trend (last 6 months)
$stmt = $pdo->query("
  SELECT DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(total_amount) as revenue
  FROM bills
  WHERE status='paid' AND paid_at IS NOT NULL
  GROUP BY month
  ORDER BY month DESC
  LIMIT 6
");
$trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$trendData = array_reverse($trendData); // oldest first


// Export as CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="healsync_report.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Section','Label','Value']);

    // Appointments by status
    $apptData = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($apptData as $row) {
        fputcsv($out, ['Appointments', $row['status'], $row['count']]);
    }

    // Revenue
    $paid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='paid'")->fetchColumn() ?: 0;
    $unpaid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='unpaid'")->fetchColumn() ?: 0;
    fputcsv($out, ['Revenue','Paid',$paid]);
    fputcsv($out, ['Revenue','Unpaid',$unpaid]);

    fclose($out);
    exit;
}

// Export as PDF
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Healsync Report',0,1,'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Appointments by Status',0,1);
    $pdf->SetFont('Arial','',11);
    $apptData = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($apptData as $row) {
        $pdf->Cell(0,8,"{$row['status']}: {$row['count']}",0,1);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Revenue Breakdown',0,1);
    $pdf->SetFont('Arial','',11);
    $paid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='paid'")->fetchColumn() ?: 0;
    $unpaid = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='unpaid'")->fetchColumn() ?: 0;
    $pdf->Cell(0,8,"Paid: $".number_format($paid,2),0,1);
    $pdf->Cell(0,8,"Unpaid: $".number_format($unpaid,2),0,1);

    $pdf->Output('D','healsync_report.pdf');
    exit;
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reports - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="bar-chart-2" class="w-6 h-6 text-indigo-600"></i>
      Reports & Analytics
    </h2>

    <!-- Appointments by Status -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
        Appointments by Status
      </h3>
      <canvas id="appointmentsChart" height="120"></canvas>
    </div>

    <!-- Revenue Breakdown -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i>
        Revenue Breakdown
      </h3>
      <canvas id="revenueChart" height="120"></canvas>
    </div>

    <!-- Revenue Trend -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="trending-up" class="w-5 h-5 text-indigo-600"></i>
        Revenue Trend (Last 6 Months)
      </h3>
      <canvas id="trendChart" height="120"></canvas>
    </div>

    <!-- Export Buttons -->
<div class="flex gap-3 mb-6">
  <a href="?export=csv"
     class="px-4 py-2 bg-green-600 text-white rounded flex items-center gap-2 hover:bg-green-700">
    <i data-lucide="download" class="w-5 h-5"></i> Export CSV
  </a>
  <a href="?export=pdf"
     class="px-4 py-2 bg-red-600 text-white rounded flex items-center gap-2 hover:bg-red-700">
    <i data-lucide="file-text" class="w-5 h-5"></i> Export PDF
  </a>
</div>

  </main>

  <script>
    lucide.createIcons();

    // Appointments Chart
    const apptCtx = document.getElementById('appointmentsChart').getContext('2d');
    new Chart(apptCtx, {
      type: 'doughnut',
      data: {
        labels: <?=json_encode(array_keys($apptData))?>,
        datasets: [{
          data: <?=json_encode(array_values($apptData))?>,
          backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ef4444']
        }]
      }
    });

    // Revenue Breakdown
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
      type: 'pie',
      data: {
        labels: ['Paid','Unpaid'],
        datasets: [{
          data: [<?= $paid ?>, <?= $unpaid ?>],
          backgroundColor: ['#22c55e','#ef4444']
        }]
      }
    });

    // Revenue Trend
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: <?=json_encode(array_column($trendData,'month'))?>,
        datasets: [{
          label: 'Revenue ($)',
          data: <?=json_encode(array_column($trendData,'revenue'))?>,
          fill: false,
          borderColor: '#6366f1',
          tension: 0.3
        }]
      }
    });
  </script>
</body>
</html>
