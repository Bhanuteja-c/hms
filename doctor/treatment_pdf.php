<?php
// doctor/treatment_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

$did = current_user_id();
$treatmentId = intval($_GET['id'] ?? 0);

if ($treatmentId <= 0) {
    die("❌ Invalid treatment ID.");
}

// Fetch treatment with patient + appointment info (must belong to this doctor)
$stmt = $pdo->prepare("
    SELECT t.*, a.date_time, 
           u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
           d.name AS doctor_name
    FROM treatments t
    JOIN appointments a ON t.appointment_id = a.id
    JOIN users u ON a.patient_id = u.id
    JOIN users d ON a.doctor_id = d.id
    WHERE t.id = :tid AND a.doctor_id = :did
    LIMIT 1
");
$stmt->execute([':tid' => $treatmentId, ':did' => $did]);
$treatment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$treatment) {
    die("❌ Treatment not found or access denied.");
}

// Start PDF
$pdf = new FPDF();
$pdf->AddPage();

// ✅ Hospital Header
pdf_header($pdf);

// Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Treatment Record', 0, 1, 'C');
$pdf->Ln(5);

// Patient & Doctor Info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, 'Patient: ' . ($treatment['patient_name'] ?? '-'), 0, 0);
$pdf->Cell(0, 8, 'Doctor: ' . ($treatment['doctor_name'] ?? '-'), 0, 1);

$pdf->Cell(100, 8, 'Email: ' . ($treatment['patient_email'] ?? '-'), 0, 0);
$pdf->Cell(0, 8, 'Phone: ' . ($treatment['patient_phone'] ?: 'N/A'), 0, 1);

$pdf->Cell(0, 8, 'Date: ' . safe_date($treatment['date_time']), 0, 1);
$pdf->Ln(8);

// Treatment Details Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Treatment', 1, 0, 'C');
$pdf->Cell(130, 10, 'Notes', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 10, $treatment['treatment_name'] ?? '-', 1, 0, 'C');

// ✅ MultiCell for long notes
$y = $pdf->GetY();
$x = $pdf->GetX();
$pdf->MultiCell(130, 10, $treatment['notes'] ?: '-', 1, 'L');
$pdf->SetXY($x + 130, $y);

$pdf->Ln(2);

// Cost Row
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Cost: ' . money($treatment['cost'] ?? 0), 1, 1, 'R');

$pdf->Ln(15);

// Footer
pdf_footer($pdf, 'Doctor');

// Output inline
$pdf->Output('I', 'Treatment_' . ($treatment['id'] ?? 0) . '.pdf');
