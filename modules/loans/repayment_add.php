<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

if (!hasRole('loan')) {
    redirect($base);
}

$loanSystem = new LoanSystem($pdo);
$loanId = $_GET['loan_id'] ?? 0;
$loan = $loanSystem->getLoanDetails($loanId);
$borrower = $loanSystem->getBorrowerDetails($loan['borrower_id']);
$borrower = $borrower['first_name']." ".$borrower['last_name'];

if (!$loan) {
    $_SESSION['error'] = "Loan not found.";
    redirect('/modules/loans/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repaymentId = $_POST['repayment_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    $reference = $_POST['reference'];
    $userId = $_SESSION['user_id'];
    $file = $_FILES['receipt'];



        $loanSystem->recordPayment($repaymentId, $amount, $method, $reference);

        if ($file['size'] > 0 && $file['error'] === 0) {
            $uploadDir = __DIR__ . '/../../uploads/receipts/';
            $fileName = uniqid('receipt_') . "_" . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $loanSystem->uploadDocument($loanId, 'repayment_receipt', '/uploads/receipts/' . $fileName, $userId);
            }
        }


        $_SESSION['toast'] = "Repayment recorded successfully.";
        $ref =  $reference;
        $_SESSION['voucher_total'] =  $amount;
        $_SESSION['voucher_date'] = date('Y-m-d');
        $_SESSION['voucher_no'] = $ref;
        $_SESSION['voucher_payee'] = $borrower;
        $_SESSION['voucher_desc']  =  'Loan Repayment';

        echo "<script> window.open('$base./page.php?ref=$ref', '_blank'); </script>";





        redirect("loan_view.php?id=$loanId");

}

$unpaidSchedules = $pdo->prepare("SELECT * FROM loan_repayments WHERE loan_id = ? AND is_paid = 0 ORDER BY due_date ASC");
$unpaidSchedules->execute([$loanId]);
$schedules = $unpaidSchedules->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Record Repayment for Loan #<?= htmlspecialchars($loan['id']) ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Repayment Schedule *</label>
            <select name="repayment_id" class="form-select" required>
                <option value="">Select Repayment</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= $s['id'] ?>">Due <?= date('M j, Y', strtotime($s['due_date'])) ?> - <?= number_format($s['amount_due'], 2) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount *</label>
            <input type="number" name="amount" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_money">Mobile Money</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Reference *</label>
            <input type="text" name="reference" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Receipt (optional)</label>
            <input type="file" name="receipt" class="form-control">
        </div>

        <?php if(hasPermission(39)){?>
 <button type="submit" class="btn btn-success">Record Repayment</button>
<?php }else{
 Echo "You do not have access to add repayment";
} ?>

    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
