<?php
require_once __DIR__ .'/../../includes/header.php';
require_once __DIR__ .'/../../classes/AccountingSystem.php';
$donation = new AccountingSystem($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $newName = 'receipt_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadDir . $newName);
        $receiptPath = $newName;
    } else {
        $receiptPath = null;
    }
    try {
        $stmt = $pdo->prepare("
            INSERT INTO donations (
                donor_id, amount, currency, donation_date, payment_method, purpose,
                project_id, is_acknowledged, receipt_number, created_by,recieptdoc
            ) VALUES (
                :donor_id, :amount, :currency, :donation_date, :payment_method, :purpose,
                :project_id, :is_acknowledged, :receipt_number, :created_by, :recieptdoc
            )
        ");

        $stmt->execute([
            'donor_id' => $_POST['donor_id'],
            'amount' => $_POST['amount'],
            'currency' => $_POST['currency'],
            'donation_date' => $_POST['donation_date'],
            'payment_method' => $_POST['payment_method'],
            'purpose' => $_POST['purpose'] ?? null,
            'project_id' => $_POST['project_id'] ?: null,
            'is_acknowledged' => $_POST['is_acknowledged'] ?? 0,
            'receipt_number' => $_POST['receipt_number'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'recieptdoc' => $receiptPath

        ]);



        $amount = $_POST['amount'];
        $id_number = 7;
        $description = 'Received Donation';
        $ref ='RCEF-'.date('Ymdhis');
        $trx = 41;

        $header = [
            'transaction_date' => date('Y-m-d'),
            'reference' => $ref,
            'description' => $description,
            'created_by' => $_SESSION['user_id']
        ];

        $lines = [];

            $lines[] = [
                'account_id' => $id_number,
                'debit' => $amount,
                'credit' => 0
            ];

            $lines[] = [
                'account_id' => $trx,
                'debit' => 0,
                'credit' => $amount
            ];



        $transactionId = $donation->createJournalEntry($header, $lines);
        $donation->postJournalEntry($transactionId, $_SESSION['user_id']);









        $_SESSION['toast'] = "Donation recorded successfully.";
        echo "<script>window.location='record_donation.php'</script>";
        exit;
    } catch (Exception $e) {
        error_log("Donation save error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to record donation.";
        echo "<script>window.location='record_donation.php'</script>";

        exit;
    }
} else {
    echo "<script>window.location='record_donation.php'</script>";
    exit;
}
