<?php
// patient/receipt_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

$pid = current_user_id();
$id  = intval($_GET['id'] ?? 0);

// Fetch bill (must belong to patient and be PAID)
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
$stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = :id");
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last payment
$stmt = $pdo->prepare("SELECT * FROM payments WHERE bill_id = :id ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':id' => $id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("❌ Payment record not found for this bill.");
}

// Log receipt generation
audit_log($pdo, $pid, 'receipt_downloaded', json_encode([
    'bill_id'    => $id,
    'payment_id' => $payment['id'],
    'method'     => $payment['method']
]));

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();

pdf_header($pdf);
pdf_receipt_info($pdf, $bill, $payment);
pdf_payment_summary($pdf, $payment);
pdf_bill_items($pdf, $items, $bill);
pdf_footer($pdf, 'Patient Portal');

// Output inline (view in browser)
$pdf->Output('I', 'Receipt_'.$bill['id'].'.pdf');
