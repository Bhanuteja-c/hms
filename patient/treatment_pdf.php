<?php
// patient/treatments_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

// Current logged-in patient
$pid = current_user_id();

// Fetch patient info
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $pid]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("❌ Patient not found.");
}

// Fetch all treatments
$stmt = $pdo->prepare("
  SELECT t.*, a.date_time, u.name AS doctor_name
  FROM treatments t
  JOIN appointments a ON t.appointment_id = a.id
  JOIN users u ON a.doctor_id = u.id
  WHERE a.patient_id = :pid
  ORDER BY a.date_time DESC
");
$stmt->execute([':pid' => $pid]);
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$treatments) {
    die("❌ No treatments found to generate PDF.");
}

// Start PDF
$pdf = new FPDF();
$pdf->AddPage();

// ✅ Hospital Header
pdf_header($pdf);

// Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Treatment History Report', 0, 1, 'C');
$pdf->Ln(5);

// Patient Info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, 'Patient: ' . ($patient['name'] ?? '-'), 0, 0);
$pdf->Cell(0, 8, 'Email: ' . ($patient['email'] ?? '-'), 0, 1);
$pdf->Cell(100, 8, 'Phone: ' . ($patient['phone'] ?? '-'), 0, 1);
$pdf->Ln(8);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C');
$pdf->Cell(40, 10, 'Doctor', 1, 0, 'C');
$pdf->Cell(45, 10, 'Treatment', 1, 0, 'C');
$pdf->Cell(35, 10, 'Cost (₹)', 1, 0, 'C');
$pdf->Cell(0, 10, 'Notes', 1, 1, 'C');

// Table Body
$pdf->SetFont('Arial', '', 11);
$totalCost = 0;

foreach ($treatments as $t) {
    $pdf->Cell(30, 10, format_datetime($t['date_time']), 1, 0, 'C');
    $pdf->Cell(40, 10, $t['doctor_name'], 1, 0, 'C');
    $pdf->Cell(45, 10, $t['treatment_name'], 1, 0, 'C');
    $pdf->Cell(35, 10, money($t['cost']), 1, 0, 'R');

    // ✅ MultiCell for notes to wrap long text
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(0, 10, $t['notes'] ?: '-', 1, 'L');
    $pdf->SetXY($x + 0, $y + 10);

    $totalCost += (float)$t['cost'];
}

// Total Row
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(115, 10, 'Total Cost', 1, 0, 'R');
$pdf->Cell(35, 10, money($totalCost), 1, 0, 'R');
$pdf->Cell(0, 10, '', 1, 1);

// Footer
$pdf->Ln(15);
pdf_footer($pdf, 'Patient Portal');

// Output inline
$pdf->Output('I', 'Treatment_History.pdf');
