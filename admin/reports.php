<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';




// Get date range parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-6 months'));
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Validate dates
if (!strtotime($startDate) || !strtotime($endDate)) {
    $startDate = date('Y-m-01', strtotime('-6 months'));
    $endDate = date('Y-m-t');
}

// Appointments by status with date filter
$apptData = $pdo->prepare("
    SELECT 
        CASE 
            WHEN status IS NULL OR status = '' THEN 'unknown'
            ELSE status 
        END as status, 
        COUNT(*) as count 
    FROM appointments 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
    ORDER BY count DESC
");
$apptData->execute([$startDate, $endDate]);
$apptData = $apptData->fetchAll(PDO::FETCH_KEY_PAIR);

// Revenue breakdown with date filter
$paid = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bills WHERE status='paid' AND DATE(paid_at) BETWEEN ? AND ?");
$paid->execute([$startDate, $endDate]);
$paid = $paid->fetchColumn();

$unpaid = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM bills WHERE status='unpaid' AND DATE(created_at) BETWEEN ? AND ?");
$unpaid->execute([$startDate, $endDate]);
$unpaid = $unpaid->fetchColumn();

// Monthly revenue trend (last 6 months) - Fixed query
$stmt = $pdo->query("
  SELECT 
    DATE_FORMAT(paid_at, '%Y-%m') as month, 
    COALESCE(SUM(total_amount), 0) as revenue
  FROM bills
  WHERE status='paid' 
    AND paid_at IS NOT NULL
    AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
  ORDER BY month ASC
");
$trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fill in missing months with zero revenue
$allMonths = [];
for ($i = 5; $i >= 0; $i--) {
    $allMonths[] = date('Y-m', strtotime("-$i month"));
}

$trendDataMap = array_column($trendData, 'revenue', 'month');
$completeTrendData = [];
foreach ($allMonths as $month) {
    $completeTrendData[] = [
        'month' => $month,
        'revenue' => $trendDataMap[$month] ?? 0
    ];
}

// Additional statistics
$totalAppointments = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(created_at) BETWEEN ? AND ?");
$totalAppointments->execute([$startDate, $endDate]);
$totalAppointments = $totalAppointments->fetchColumn();

$totalRevenue = $paid + $unpaid;
$collectionRate = $totalRevenue > 0 ? ($paid / $totalRevenue) * 100 : 0;


// Export as CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="healsync_report_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Report Period', $startDate . ' to ' . $endDate]);
    fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
    fputcsv($out, []); // Empty row
    fputcsv($out, ['Section','Label','Value']);

    // Appointments by status
    foreach ($apptData as $status => $count) {
        fputcsv($out, ['Appointments', ucfirst($status), $count]);
    }

    // Revenue
    fputcsv($out, ['Revenue','Paid', '₹' . number_format($paid, 2)]);
    fputcsv($out, ['Revenue','Unpaid', '₹' . number_format($unpaid, 2)]);
    fputcsv($out, ['Revenue','Total', '₹' . number_format($totalRevenue, 2)]);
    fputcsv($out, ['Revenue','Collection Rate', number_format($collectionRate, 1) . '%']);

    // Summary
    fputcsv($out, []);
    fputcsv($out, ['Summary','Total Appointments', $totalAppointments]);
    fputcsv($out, ['Summary','Paid Revenue', '₹' . number_format($paid, 2)]);
    fputcsv($out, ['Summary','Outstanding', '₹' . number_format($unpaid, 2)]);

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
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Period: ' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)),0,1,'C');
    $pdf->Cell(0,6,'Generated: ' . date('M d, Y H:i:s'),0,1,'C');
    $pdf->Ln(10);

    // Summary Section
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'Summary',0,1);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,"Total Appointments: " . number_format($totalAppointments),0,1);
    $pdf->Cell(0,6,"Paid Revenue: ₹" . number_format($paid,2),0,1);
    $pdf->Cell(0,6,"Outstanding: ₹" . number_format($unpaid,2),0,1);
    $pdf->Cell(0,6,"Collection Rate: " . number_format($collectionRate,1) . "%",0,1);
    $pdf->Ln(5);

    // Appointments by Status
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'Appointments by Status',0,1);
    $pdf->SetFont('Arial','',10);
    foreach ($apptData as $status => $count) {
        $pdf->Cell(0,6,ucfirst($status) . ": " . $count,0,1);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'Revenue Breakdown',0,1);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,"Paid: ₹" . number_format($paid,2),0,1);
    $pdf->Cell(0,6,"Unpaid: ₹" . number_format($unpaid,2),0,1);
    $pdf->Cell(0,6,"Total: ₹" . number_format($totalRevenue,2),0,1);

    $pdf->Output('D','healsync_report_' . date('Y-m-d') . '.pdf');
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
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold flex items-center gap-3">
          <i data-lucide="bar-chart-2" class="w-8 h-8 text-indigo-600"></i>
          Reports & Analytics
        </h2>
        <div class="text-sm text-gray-500">
          Period: <?= date('M d, Y', strtotime($startDate)) ?> - <?= date('M d, Y', strtotime($endDate)) ?>
        </div>
      </div>

      <!-- Date Range Filter -->
      <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
          Filter by Date Range
        </h3>
        <form method="GET" class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-48">
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?= e($startDate) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div class="flex-1 min-w-48">
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?= e($endDate) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <button type="submit" 
                  class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center gap-2">
            <i data-lucide="search" class="w-4 h-4"></i>
            Apply Filter
          </button>
          <a href="reports.php" 
             class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Reset
          </a>
        </form>
      </div>

      <!-- Summary Cards -->
      <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Total Appointments</p>
              <p class="text-2xl font-bold text-gray-900"><?= number_format($totalAppointments) ?></p>
            </div>
            <i data-lucide="calendar" class="w-8 h-8 text-blue-500"></i>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Paid Revenue</p>
              <p class="text-2xl font-bold text-green-600">₹<?= number_format($paid, 2) ?></p>
            </div>
            <i data-lucide="check-circle" class="w-8 h-8 text-green-500"></i>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Outstanding</p>
              <p class="text-2xl font-bold text-red-600">₹<?= number_format($unpaid, 2) ?></p>
            </div>
            <i data-lucide="alert-circle" class="w-8 h-8 text-red-500"></i>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Collection Rate</p>
              <p class="text-2xl font-bold text-purple-600"><?= number_format($collectionRate, 1) ?>%</p>
            </div>
            <i data-lucide="percent" class="w-8 h-8 text-purple-500"></i>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Appointments by Status -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
            Appointments by Status
          </h3>
          <div class="h-80">
            <canvas id="appointmentsChart"></canvas>
          </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i>
            Revenue Breakdown
          </h3>
          <div class="h-80">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Revenue Trend -->
      <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <i data-lucide="trending-up" class="w-5 h-5 text-indigo-600"></i>
          Revenue Trend (Last 6 Months)
        </h3>
        <div class="h-80">
          <canvas id="trendChart"></canvas>
        </div>
      </div>

      <!-- Export Section -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <i data-lucide="download" class="w-5 h-5 text-indigo-600"></i>
          Export Reports
        </h3>
        <div class="flex flex-wrap gap-4">
          <a href="?export=csv&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"
             class="px-6 py-3 bg-green-600 text-white rounded-lg flex items-center gap-2 hover:bg-green-700 transition-colors duration-200">
            <i data-lucide="download" class="w-5 h-5"></i>
            Export CSV
          </a>
          <a href="?export=pdf&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"
             class="px-6 py-3 bg-red-600 text-white rounded-lg flex items-center gap-2 hover:bg-red-700 transition-colors duration-200">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            Export PDF
          </a>
        </div>
      </div>
    </div>

  </main>

  <script>
    lucide.createIcons();

    // Appointments Chart - Enhanced
    const apptCtx = document.getElementById('appointmentsChart').getContext('2d');
    const apptData = <?= json_encode($apptData) ?>;
    
    // Filter out zero values for cleaner display
    const filteredApptData = Object.entries(apptData).filter(([key, value]) => value > 0);
    
    new Chart(apptCtx, {
      type: 'doughnut',
      data: {
        labels: filteredApptData.map(([key]) => key.charAt(0).toUpperCase() + key.slice(1)),
        datasets: [{
          data: filteredApptData.map(([key, value]) => value),
          backgroundColor: [
            '#22c55e',  // green for completed
            '#3b82f6',  // blue for approved
            '#f59e0b',  // yellow for pending
            '#ef4444',  // red for cancelled
            '#6b7280'   // gray for rejected
          ],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
              }
            }
          }
        }
      }
    });

    // Revenue Breakdown - Enhanced
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
      type: 'pie',
      data: {
        labels: ['Paid', 'Unpaid'],
        datasets: [{
          data: [<?= $paid ?>, <?= $unpaid ?>],
          backgroundColor: ['#22c55e', '#ef4444'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ₹' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
              }
            }
          }
        }
      }
    });

    // Revenue Trend - Enhanced
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendData = <?= json_encode($completeTrendData) ?>;
    
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: trendData.map(item => {
          const date = new Date(item.month + '-01');
          return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
          label: 'Revenue (₹)',
          data: trendData.map(item => parseFloat(item.revenue)),
          fill: true,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Revenue: ₹' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '₹' + value.toLocaleString();
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.1)'
            }
          },
          x: {
            grid: {
              color: 'rgba(0, 0, 0, 0.1)'
            }
          }
        }
      }
    });
  </script>
</body>
</html>
