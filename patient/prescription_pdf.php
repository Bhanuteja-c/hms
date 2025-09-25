<?php
// patient/prescription_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/lib/fpdf.php';

$pid = current_user_id();
$id = intval($_GET['id'] ?? 0);

// Fetch prescription if belongs to patient
$stmt = $pdo->prepare("
  SELECT p.*, a.date_time, u.name AS doctor_name
  FROM prescriptions p
  JOIN appointments a ON p.appointment_id = a.id
  JOIN users u ON a.doctor_id = u.id
  WHERE p.id = :id AND a.patient_id = :pid
");
$stmt->execute([':id'=>$id, ':pid'=>$pid]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
  die("Not found or unauthorized");
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Healsync - Prescription',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Doctor: '.$p['doctor_name'],0,1);
$pdf->Cell(0,10,'Date: '.format_datetime($p['date_time']),0,1);
$pdf->Ln(5);

$pdf->Cell(0,10,'Medicine: '.$p['medicine'],0,1);
$pdf->Cell(0,10,'Dosage: '.$p['dosage'],0,1);
$pdf->Cell(0,10,'Duration: '.$p['duration'],0,1);
$pdf->MultiCell(0,10,'Instructions: '.$p['instructions']);

$pdf->Output('D', 'Prescription_'.$p['id'].'.pdf');
