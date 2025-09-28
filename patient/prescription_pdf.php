<?php
// patient/prescription_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

$pid = current_user_id();
$id  = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("❌ Invalid prescription ID.");
}

// Fetch prescription with appointment, doctor, patient
$stmt = $pdo->prepare("
  SELECT pr.id, pr.created_at,
         d.name AS doctor_name, d.email AS doctor_email,
         p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
         a.date_time AS appointment_date
  FROM prescriptions pr
  JOIN appointments a ON pr.appointment_id = a.id
  JOIN users d ON a.doctor_id = d.id
  JOIN users p ON a.patient_id = p.id
  WHERE pr.id = :id AND a.patient_id = :pid
  LIMIT 1
");
$stmt->execute([':id' => $id, ':pid' => $pid]);
$presc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$presc) {
    die("❌ Prescription not found or unauthorized.");
}

// Fetch prescribed medicines
$stmt = $pdo->prepare("SELECT * FROM prescription_items WHERE prescription_id = :pid");
$stmt->execute([':pid' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    die("❌ No medicines found in this prescription.");
}

// PDF start
$pdf = new FPDF();
$pdf->AddPage();

// Header
pdf_header($pdf);

// Title
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Prescription',0,1,'C');
$pdf->Ln(5);

// Info
$pdf->SetFont('Arial','',12);
$pdf->Cell(100,8,'Prescription ID: '.$presc['id'],0,0);
$pdf->Cell(0,8,'Date: '.format_datetime($presc['created_at']),0,1);

$pdf->Cell(100,8,'Doctor: '.$presc['doctor_name'],0,0);
$pdf->Cell(0,8,'Appointment: '.format_datetime($presc['appointment_date']),0,1);

$pdf->Cell(100,8,'Patient: '.$presc['patient_name'],0,0);
$pdf->Cell(0,8,'Phone: '.$presc['patient_phone'],0,1);

$pdf->Cell(100,8,'Email: '.$presc['patient_email'],0,1);
$pdf->Ln(10);

// Medicines Table
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Medicine',1,0,'C');
$pdf->Cell(40,10,'Dosage',1,0,'C');
$pdf->Cell(40,10,'Duration',1,0,'C');
$pdf->Cell(50,10,'Instructions',1,1,'C');

$pdf->SetFont('Arial','',12);
foreach ($items as $it) {
    $pdf->Cell(60,10,$it['medicine'],1,0);
    $pdf->Cell(40,10,$it['dosage'],1,0,'C');
    $pdf->Cell(40,10,$it['duration'],1,0,'C');
    $pdf->Cell(50,10,$it['instructions'],1,1);
}

// Footer
$pdf->Ln(15);
$pdf->SetFont('Arial','I',11);
$pdf->MultiCell(0,8,"This prescription is issued by Dr. ".$presc['doctor_name'].".\nPlease follow the instructions carefully.",0,'C');

// Output
$pdf->Output('I','Prescription_'.$presc['id'].'.pdf');
