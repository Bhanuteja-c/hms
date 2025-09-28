<?php
// models/ReceiptGenerator.php
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

class ReceiptGenerator {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function generate(int $billId, string $role = 'patient'): void {
        // Fetch bill
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
                   d.name AS doctor_name
            FROM bills b
            JOIN users p ON b.patient_id = p.id
            JOIN users d ON b.doctor_id = d.id
            WHERE b.id = :id AND b.status='paid'
        ");
        $stmt->execute([':id' => $billId]);
        $bill = $stmt->fetch();

        if (!$bill) {
            die("❌ Receipt not available (bill unpaid or not found).");
        }

        // Payment
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE bill_id=:id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':id' => $billId]);
        $payment = $stmt->fetch();

        if (!$payment) die("❌ Payment record not found.");

        // Bill items (for patient only)
        $items = [];
        if ($role === 'patient') {
            $stmt = $this->pdo->prepare("SELECT * FROM bill_items WHERE bill_id=:id");
            $stmt->execute([':id' => $billId]);
            $items = $stmt->fetchAll();
        }

        // Build PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        pdf_header($pdf);
        pdf_receipt_info($pdf, $bill, $payment);
        pdf_payment_summary($pdf, $payment);
        pdf_bill_items($pdf, $items, $bill);
        pdf_footer($pdf, $role === 'patient' ? 'Healsync' : 'Reception');

        // Output inline (I = inline, D = download)
        $pdf->Output('I','Receipt_'.$bill['id'].'.pdf');
    }
}
