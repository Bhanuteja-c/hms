<?php
// models/Payment.php
class Payment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Get the latest payment for a bill */
    public function lastForBill(int $billId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payments WHERE bill_id=:id ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':id' => $billId]);
        $payment = $stmt->fetch();
        return $payment ?: null;
    }
}
