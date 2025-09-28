<?php
// patient/receipt_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

$pid = current_user_id();
$id  = intval($_GET['id'] ?? 0);

// Fetch bill (must be PAID)
$stmt = $pdo->prepare("
  SELECT b.*, 
         d.name AS doctor_name, 
         p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone
  FROM bills b
  JOIN users d ON b.doctor_id = d.id
  JOIN users p ON b.patient_id = p.id
  WHERE b.id = :id AND b.patient_id = :pid AND b.status = 'paid'
");
$stmt->execute([':id' => $id, ':pid' => $pid]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("❌ Receipt not available. Either not found or bill unpaid.");
}

// Fetch bill items
$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id=:id");
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last payment
$stmt = $pdo->prepare("SELECT * FROM payments WHERE bill_id=:id ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':id' => $id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("❌ Payment record not found for this bill.");
}

// PDF
$pdf = new FPDF();
$pdf->AddPage();

// Hospital Header
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Healsync Hospital',0,1,'C');
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,'123 Main Street, City - Phone: +91-9876543210',0,1,'C');
$pdf->Cell(0,6,'Email: support@healsync.com | www.healsync.com',0,1,'C');
$pdf->Ln(8);

// Title
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Payment Receipt',0,1,'C');
$pdf->Ln(5);

// Bill & Payment Info
$pdf->SetFont('Arial','',12);
$pdf->Cell(100,8,'Receipt ID: '.$payment['id'],0,0);
$pdf->Cell(0,8,'Date: '.date('d M Y H:i', strtotime($payment['created_at'] ?? $bill['paid_at'])),0,1);

$pdf->Cell(100,8,'Bill ID: '.$bill['id'],0,0);
$pdf->Cell(0,8,'Doctor: '.$bill['doctor_name'],0,1);

$pdf->Cell(100,8,'Patient: '.$bill['patient_name'],0,0);
$pdf->Cell(0,8,'Phone: '.$bill['patient_phone'],0,1);

$pdf->Cell(100,8,'Email: '.$bill['patient_email'],0,1);
$pdf->Ln(10);

// Payment Summary Table
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Amount Paid',1,0,'C');
$pdf->Cell(60,10,'Method',1,0,'C');
$pdf->Cell(70,10,'Transaction ID',1,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(60,10,'₹'.number_format($payment['amount'],2),1,0,'C');
$pdf->Cell(60,10,ucfirst(str_replace('_',' ',$payment['method'])),1,0,'C');
$pdf->Cell(70,10,$payment['transaction_id'],1,1,'C');

$pdf->Ln(10);

// Bill Items
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Description',1,0,'C');
$pdf->Cell(60,10,'Amount (₹)',1,1,'C');

$pdf->SetFont('Arial','',12);
foreach ($items as $it) {
    $pdf->Cell(130,10,$it['description'],1,0);
    $pdf->Cell(60,10,number_format($it['amount'],2),1,1,'R');
}

// Total
$pdf->SetFont('Arial','B',12);
$pdf->Cell(130,10,'Total Paid',1,0);
$pdf->Cell(60,10,'₹'.number_format($bill['total_amount'],2),1,1,'R');

$pdf->Ln(15);

// Footer Notes
$pdf->SetFont('Arial','I',11);
$pdf->MultiCell(0,8,"This receipt acknowledges full payment for the above bill.\nThank you for choosing Healsync!",0,'C');

$pdf->Ln(20);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,10,'Authorized by Healsync',0,1,'R');

// Output inline (can be printed or downloaded)
$pdf->Output('I','Receipt_'.$bill['id'].'.pdf');
