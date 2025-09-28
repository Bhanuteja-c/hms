<?php
// reception/receipt_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

$id = intval($_GET['id'] ?? 0);

// Fetch bill (must be paid)
$stmt = $pdo->prepare("
  SELECT b.*, 
         p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
         d.name AS doctor_name
  FROM bills b
  JOIN users p ON b.patient_id = p.id
  JOIN users d ON b.doctor_id = d.id
  WHERE b.id = :id AND b.status = 'paid'
");
$stmt->execute([':id' => $id]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("❌ Receipt not available (bill unpaid or not found).");
}

// Fetch last payment
$stmt = $pdo->prepare("SELECT * FROM payments WHERE bill_id = :id ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':id' => $id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("❌ Payment record not found for this bill.");
}

// Log receipt generation
audit_log($pdo, current_user_id(), 'receipt_generated', json_encode([
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

// Reception does not need bill item breakdown here (optional)
// If you want items, you can fetch like in patient and call pdf_bill_items()

pdf_footer($pdf, 'Reception');

// Output inline
$pdf->Output('I', 'Receipt_'.$bill['id'].'.pdf');
