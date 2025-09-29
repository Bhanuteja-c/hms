<?php
// includes/pdf_helpers.php
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
require_once __DIR__ . '/functions.php';

// Reuse global helpers when available to avoid redeclaration
if (!function_exists('safe_date')) {
    /**
     * Format date safely
     */
    function safe_date(?string $dt, string $format = 'd M Y H:i'): string {
        if (empty($dt)) return '-';
        return date($format, strtotime($dt));
    }
}

if (!function_exists('money')) {
    /**
     * Format money in INR
     */
    function money($amount): string {
        return '₹' . number_format((float)$amount, 2);
    }
}

/**
 * Hospital Header
 */
function pdf_header(FPDF $pdf, string $title = 'Healsync Hospital'): void {
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, '123 Main Street, City - Phone: +91-9876543210', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Email: support@healsync.com | www.healsync.com', 0, 1, 'C');
    $pdf->Ln(8);
}

/**
 * Payment Receipt Info
 */
function pdf_receipt_info(FPDF $pdf, array $bill, array $payment): void {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 8, 'Receipt ID: ' . ($payment['id'] ?? '-'), 0, 0);
    $pdf->Cell(0, 8, 'Date: ' . safe_date($payment['created_at'] ?? $bill['paid_at']), 0, 1);

    $pdf->Cell(100, 8, 'Bill ID: ' . ($bill['id'] ?? '-'), 0, 0);
    $pdf->Cell(0, 8, 'Doctor: ' . ($bill['doctor_name'] ?? '-'), 0, 1);

    $pdf->Cell(100, 8, 'Patient: ' . ($bill['patient_name'] ?? '-'), 0, 0);
    if (!empty($bill['patient_phone'])) {
        $pdf->Cell(0, 8, 'Phone: ' . $bill['patient_phone'], 0, 1);
    } else {
        $pdf->Ln(8);
    }

    $pdf->Cell(100, 8, 'Email: ' . ($bill['patient_email'] ?? '-'), 0, 1);
    $pdf->Ln(10);
}

/**
 * Payment Summary Table
 */
function pdf_payment_summary(FPDF $pdf, array $payment): void {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Amount Paid', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Method', 1, 0, 'C');
    $pdf->Cell(70, 10, 'Transaction ID', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(60, 10, money($payment['amount'] ?? 0), 1, 0, 'C');
    $pdf->Cell(60, 10, ucfirst(str_replace('_', ' ', $payment['method'] ?? 'Unknown')), 1, 0, 'C');
    $pdf->Cell(70, 10, $payment['transaction_id'] ?? '-', 1, 1, 'C');
    $pdf->Ln(10);
}

/**
 * Bill Items Table
 */
function pdf_bill_items(FPDF $pdf, array $items, array $bill): void {
    if (empty($items)) return;

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 10, 'Description', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Amount (₹)', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    foreach ($items as $it) {
        $pdf->Cell(130, 10, $it['description'] ?? '-', 1, 0);
        $pdf->Cell(60, 10, money($it['amount'] ?? 0), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 10, 'Total Paid', 1, 0);
    $pdf->Cell(60, 10, money($bill['total_amount'] ?? 0), 1, 1, 'R');
    $pdf->Ln(15);
}

/**
 * Footer Note
 */
function pdf_footer(FPDF $pdf, string $roleText = 'Healsync'): void {
    $pdf->SetFont('Arial', 'I', 11);
    $pdf->MultiCell(
        0,
        8,
        "This receipt acknowledges full payment for the above bill.\nThank you for choosing Healsync!",
        0,
        'C'
    );

    $pdf->Ln(20);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 10, 'Authorized by ' . $roleText, 0, 1, 'R');
}
