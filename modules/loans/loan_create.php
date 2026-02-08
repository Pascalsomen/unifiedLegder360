<?php
require_once '../../includes/header.php';
require_once '../../classes/LoanSystem.php';
$loanSystem = new LoanSystem($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = $loanSystem->createLoan($_POST, $_SESSION['user_id']);
    $_SESSION['toast'] = "Loan created successfully";
    redirect("$base_url/loans/loan_view.php?id=$loanId");
}

$stmt = $pdo->prepare("SELECT * FROM borrower");
$stmt->execute();
$borrowers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="post" class="container mt-4">
    <h4>Create New Loan</h4>
    <div class="mb-3">
        <label>Select</label>
        <select name="borrower_id" class="form-control">
            <option value="">Select Borrower</option>
            <?php foreach ($borrowers as $borrower): ?>

            <option value="<?php echo htmlspecialchars($borrower['id']); ?>"> <?php echo htmlspecialchars($borrower['id_number']); ?> - <?php echo htmlspecialchars($borrower['first_name']) . ' ' . htmlspecialchars($borrower['last_name']); ?></option>
        <?php endforeach; ?>

        </select>
    </div>
    <div class="mb-3">
        <label>Amount</label>
        <input type="number" name="amount" step="0.01" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Interest Rate (%)</label>
        <input type="number" name="interest_rate" step="0.01" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Term (Months)</label>
        <input type="number" name="term_months" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Purpose</label>
        <textarea name="purpose" class="form-control"></textarea>
    </div>

    <?php if(hasPermission(35)){?>
        <button type="submit" class="btn btn-primary">Create Loan</button>
<?php }else{
 Echo "You do not have access to add new loan";
} ?>

</form>
<?php require_once '../../includes/footer.php'; ?>
