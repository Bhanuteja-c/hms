<?php
// reception/get_bill_details.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$bill_id = intval($_GET['id'] ?? 0);

if (!$bill_id) {
    json_error('Invalid bill ID');
}

// Fetch bill details with patient and doctor info
$stmt = $pdo->prepare("
    SELECT b.*, 
           p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
           d.name AS doctor_name, d.specialty AS doctor_specialty
    FROM bills b
    JOIN users p ON b.patient_id = p.id
    JOIN users d ON b.doctor_id = d.id
    WHERE b.id = :bill_id
");
$stmt->execute([':bill_id' => $bill_id]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    json_error('Bill not found');
}

// Get bill items (treatments/prescriptions that generated this bill)
$stmt = $pdo->prepare("
    SELECT bi.description, bi.amount, bi.created_at
    FROM bill_items bi
    WHERE bi.bill_id = :bill_id
    ORDER BY bi.created_at ASC
");
$stmt->execute([':bill_id' => $bill_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate HTML content
ob_start();
?>
<div class="space-y-6">
    <!-- Bill Header -->
    <div class="bg-indigo-50 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-xl font-semibold text-indigo-900">Bill #<?= e($bill['id']) ?></h4>
            <span class="px-3 py-1 bg-<?= $bill['status'] === 'paid' ? 'green' : 'red' ?>-100 text-<?= $bill['status'] === 'paid' ? 'green' : 'red' ?>-800 rounded-full text-sm font-medium">
                <?= ucfirst($bill['status']) ?>
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-indigo-600 font-medium">Created:</span>
                <span class="text-indigo-800"><?= e(format_datetime($bill['created_at'])) ?></span>
            </div>
            <div>
                <span class="text-indigo-600 font-medium">Total Amount:</span>
                <span class="text-indigo-800 font-bold text-lg"><?= money($bill['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <!-- Patient & Doctor Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i>
                Patient Information
            </h5>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600 font-medium">Name:</span> <span class="text-gray-800"><?= e($bill['patient_name']) ?></span></div>
                <div><span class="text-gray-600 font-medium">Email:</span> <span class="text-gray-800"><?= e($bill['patient_email']) ?></span></div>
                <?php if ($bill['patient_phone']): ?>
                <div><span class="text-gray-600 font-medium">Phone:</span> <span class="text-gray-800"><?= e($bill['patient_phone']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-4 h-4 text-indigo-600"></i>
                Doctor Information
            </h5>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600 font-medium">Name:</span> <span class="text-gray-800"><?= e($bill['doctor_name']) ?></span></div>
                <?php if ($bill['doctor_specialty']): ?>
                <div><span class="text-gray-600 font-medium">Specialty:</span> <span class="text-gray-800"><?= e($bill['doctor_specialty']) ?></span></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bill Items -->
    <div>
        <h5 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="list" class="w-4 h-4 text-indigo-600"></i>
            Bill Items (<?= count($items) ?>)
        </h5>
        <?php if ($items): ?>
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900"><?= e($item['description']) ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= money($item['amount']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= e(format_datetime($item['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">Total</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900"><?= money($bill['total_amount']) ?></td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>
                    <span class="text-yellow-800">No bill items found</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment Status -->
    <?php if ($bill['status'] === 'paid'): ?>
        <div class="bg-green-50 rounded-lg p-4">
            <h5 class="font-semibold text-green-900 mb-2 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Payment Information
            </h5>
            <div class="text-sm text-green-800">
                <p>This bill has been paid on <?= e(format_datetime($bill['paid_at'])) ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-red-50 rounded-lg p-4">
            <h5 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Payment Pending
            </h5>
            <div class="text-sm text-red-800">
                <p>This bill is awaiting payment. Use the "Mark Paid" button to process the payment.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-4 border-t">
        <?php if ($bill['status'] === 'paid'): ?>
            <a href="receipt_pdf.php?id=<?= e($bill['id']) ?>" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i data-lucide="download" class="w-4 h-4"></i>
                Download Receipt
            </a>
        <?php endif; ?>
        <button onclick="closeBillModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Close
        </button>
    </div>
</div>
<?php
$html = ob_get_clean();

json_success('Bill details loaded', ['html' => $html]);
