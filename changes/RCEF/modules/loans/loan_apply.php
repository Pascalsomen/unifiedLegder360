<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

$loanSystem = new LoanSystem($pdo);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'borrower_id' => $userId,
            'amount' => $_POST['amount'],
            'interest_rate' => $_POST['interest_rate'] ?? 0,
            'term_months' => $_POST['term_months'] ?? null,
            'purpose' => $_POST['purpose']
        ];
        $loanId = $loanSystem->createLoan($data);
        $_SESSION['success'] = "Loan application submitted!";
        redirect("/modules/loans/view.php?id=$loanId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2><i class="bi bi-cash-coin"></i> Apply for Loan</h2>
    <form method="post" class="card card-body shadow-sm p-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Loan Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Interest Rate (%)</label>
                <input type="number" step="0.01" name="interest_rate" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Term (Months)</label>
                <input type="number" name="term_months" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label>Purpose</label>
            <textarea name="purpose" class="form-control" rows="3"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-send-check"></i> Submit Application
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
