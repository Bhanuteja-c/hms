<?php
// models/InvoiceGenerator.php
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
require_once __DIR__ . '/../includes/pdf_helpers.php';

class InvoiceGenerator {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function generate(int $billId): void {
        // Fetch bill
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
                   d.name AS doctor_name
            FROM bills b
            JOIN users p ON b.patient_id = p.id
            JOIN users d ON b.doctor_id = d.id
            WHERE b.id = :id
        ");
        $stmt->execute([':id' => $billId]);
        $bill = $stmt->fetch();

        if (!$bill) {
            die("❌ Invoice not available (bill not found).");
        }

        // Fetch items
        $stmt = $this->pdo->prepare("SELECT * FROM bill_items WHERE bill_id=:id");
        $stmt->execute([':id' => $billId]);
        $items = $stmt->fetchAll();

        // PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        pdf_header($pdf);

        // Invoice info
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Invoice / Bill',0,1,'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(100,8,'Bill ID: '.$bill['id'],0,0);
        $pdf->Cell(0,8,'Date: '.date('d M Y H:i', strtotime($bill['created_at'])),0,1);

        $pdf->Cell(100,8,'Doctor: '.$bill['doctor_name'],0,0);
        $pdf->Cell(0,8,'Patient: '.$bill['patient_name'],0,1);

        $pdf->Cell(100,8,'Email: '.$bill['patient_email'],0,0);
        $pdf->Cell(0,8,'Phone: '.$bill['patient_phone'],0,1);
        $pdf->Ln(10);

        // Bill Items Table
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
        $pdf->Cell(130,10,'Total',1,0);
        $pdf->Cell(60,10,'₹'.number_format($bill['total_amount'],2),1,1,'R');

        $pdf->Ln(15);

        // Notes
        $pdf->SetFont('Arial','I',11);
        if ($bill['status'] === 'paid') {
            $pdf->MultiCell(0,8,"✅ This bill has already been paid.\nReceipt can be generated separately.",0,'C');
        } else {
            $pdf->MultiCell(0,8,"⚠ Payment pending.\nPlease pay at reception or via online portal.",0,'C');
        }

        $pdf->Output('I','Invoice_'.$bill['id'].'.pdf');
    }
}
