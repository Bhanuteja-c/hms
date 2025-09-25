<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_once __DIR__ . '/../vendor/fpdf/fpdf.php'; // Ensure FPDF is installed

$pid = current_user_id();
$bill_id = intval($_GET['id'] ?? 0);

// Fetch bill
$stmt = $pdo->prepare("SELECT * FROM bills WHERE id=:id AND patient_id=:pid");
$stmt->execute([':id'=>$bill_id, ':pid'=>$pid]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("Bill not found.");
}

// Fetch patient
$stmt = $pdo->prepare("SELECT name, email, phone, address FROM users WHERE id=:id");
$stmt->execute([':id'=>$pid]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch treatments with doctor info
$stmt = $pdo->prepare("
    SELECT t.treatment_name, t.date, t.cost, t.notes, a.id AS appointment_id, d.name AS doctor_name
    FROM treatments t
    JOIN appointments a ON t.appointment_id=a.id
    JOIN users d ON a.doctor_id=d.id
    WHERE a.patient_id=:pid
");
$stmt->execute([':pid'=>$pid]);
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch prescriptions with doctor info
$stmt = $pdo->prepare("
    SELECT p.medicine, p.dosage, p.duration, p.instructions, a.id AS appointment_id, d.name AS doctor_name, a.date_time
    FROM prescriptions p
    JOIN appointments a ON p.appointment_id=a.id
    JOIN users d ON a.doctor_id=d.id
    WHERE a.patient_id=:pid
");
$stmt->execute([':pid'=>$pid]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate unique receipt number
$receiptNumber = sprintf("HMS-%s-%06d", date('Y'), $bill['id']);

class PDF extends FPDF {
    function Header() {
        // Logo
        if (file_exists(__DIR__ . '/../assets/img/logo.png')) {
            $this->Image(__DIR__ . '/../assets/img/logo.png',10,6,20);
        }
        // Hospital Name
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Healsync Hospital',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'123 Health Street, Wellness City, Careland',0,1,'C');
        $this->Ln(5);
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-35);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,8,'Thank you for choosing Healsync. Get well soon!',0,1,'C');
        $this->Cell(0,8,'Generated on '.date('d M Y H:i'),0,1,'C');

        // Signature / Stamp
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->Cell(95,10,'_________________________',0,0,'C');
        $this->Cell(95,10,'_________________________',0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(95,6,'Authorized Signature',0,0,'C');
        $this->Cell(95,6,'Hospital Stamp',0,1,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Bill Receipt',0,1,'C');
$pdf->Ln(5);

// Patient Info + Receipt Number
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Receipt No: ".$receiptNumber,0,1);
$pdf->Cell(0,8,"Bill ID: #".$bill['id'],0,1);
$pdf->Cell(0,8,"Patient: ".$patient['name']." (".$patient['email'].")",0,1);
$pdf->Cell(0,8,"Phone: ".$patient['phone'],0,1);
$pdf->Cell(0,8,"Address: ".$patient['address'],0,1);
$pdf->Ln(8);

// === Treatments Section ===
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Treatments",0,1);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(20,10,'Appt ID',1,0,'C',true);
$pdf->Cell(40,10,'Doctor',1,0,'C',true);
$pdf->Cell(40,10,'Treatment',1,0,'C',true);
$pdf->Cell(30,10,'Date',1,0,'C',true);
$pdf->Cell(30,10,'Cost',1,0,'C',true);
$pdf->Cell(30,10,'Notes',1,1,'C',true);

$pdf->SetFont('Arial','',11);
if ($treatments) {
    foreach ($treatments as $t) {
        $pdf->Cell(20,10,$t['appointment_id'],1);
        $pdf->Cell(40,10,substr($t['doctor_name'],0,15),1);
        $pdf->Cell(40,10,substr($t['treatment_name'],0,15),1);
        $pdf->Cell(30,10,date('d M Y', strtotime($t['date'])),1);
        $pdf->Cell(30,10,'$'.number_format($t['cost'],2),1);
        $pdf->Cell(30,10,substr($t['notes'],0,15),1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190,10,'No treatments found',1,1,'C');
}
$pdf->Ln(8);

// === Prescriptions Section ===
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Prescriptions",0,1);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(20,10,'Appt ID',1,0,'C',true);
$pdf->Cell(40,10,'Doctor',1,0,'C',true);
$pdf->Cell(40,10,'Medicine',1,0,'C',true);
$pdf->Cell(30,10,'Dosage',1,0,'C',true);
$pdf->Cell(30,10,'Duration',1,0,'C',true);
$pdf->Cell(30,10,'Instr.',1,1,'C',true);

$pdf->SetFont('Arial','',11);
if ($prescriptions) {
    foreach ($prescriptions as $p) {
        $pdf->Cell(20,10,$p['appointment_id'],1);
        $pdf->Cell(40,10,substr($p['doctor_name'],0,15),1);
        $pdf->Cell(40,10,substr($p['medicine'],0,15),1);
        $pdf->Cell(30,10,$p['dosage'],1);
        $pdf->Cell(30,10,$p['duration'],1);
        $pdf->Cell(30,10,substr($p['instructions'],0,12),1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190,10,'No prescriptions found',1,1,'C');
}
$pdf->Ln(8);

// === Totals & Status ===
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Total Bill: $".number_format($bill['total_amount'],2),0,1,'R');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Status: ".ucfirst($bill['status']),0,1,'R');
if ($bill['status']==='paid') {
    $pdf->Cell(0,8,"Paid on: ".date('d M Y', strtotime($bill['paid_at'])),0,1,'R');
}

$pdf->Output('I', "bill_{$bill['id']}.pdf");
