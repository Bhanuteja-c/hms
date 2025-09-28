<?php
// patient/bill_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php'; // ✅ Use shared helpers

$pid = current_user_id();
$id  = intval($_GET['id'] ?? 0);

// Fetch bill (any status: paid/unpaid, since it's just preview)
$stmt = $pdo->prepare("
  SELECT b.*, 
         d.name AS doctor_name, 
         p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone
  FROM bills b
  JOIN users d ON b.doctor_id = d.id
  JOIN users p ON b.patient_id = p.id
  WHERE b.id = :id AND b.patient_id = :pid
");
$stmt->execute([':id' => $id, ':pid' => $pid]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("❌ Bill not found or unauthorized.");
}

// Fetch bill items
$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id=:id");
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new FPDF();
$pdf->AddPage();

// ✅ Hospital header (shared)
pdf_header($pdf);

// Title
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Bill Preview',0,1,'C');
$pdf->Ln(5);

// Bill Info
$pdf->SetFont('Arial','',12);
$pdf->Cell(100,8,'Bill ID: '.$bill['id'],0,0);
$pdf->Cell(0,8,'Doctor: '.$bill['doctor_name'],0,1);

$pdf->Cell(100,8,'Patient: '.$bill['patient_name'],0,0);
$pdf->Cell(0,8,'Phone: '.$bill['patient_phone'],0,1);

$pdf->Cell(100,8,'Email: '.$bill['patient_email'],0,0);
$pdf->Cell(0,8,'Status: '.ucfirst($bill['status']),0,1);
$pdf->Ln(10);

// ✅ Bill Items (shared)
pdf_bill_items($pdf, $items, $bill);

// Footer Note
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,8,"This is a bill preview. Payment status: ".ucfirst($bill['status']).".",0,'C');

// Output inline
$pdf->Output('I','Bill_'.$bill['id'].'.pdf');
