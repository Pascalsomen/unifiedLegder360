<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="container mt-4">
    <h2><i class="bi bi-credit-card"></i> Make a Loan Payment</h2>
    <form method="post" action="process_repay.php">
        <div class="mb-3">
            <label class="form-label">Loan ID *</label>
            <input type="number" name="loan_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Repayment Schedule ID *</label>
            <input type="number" name="repayment_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Amount Paid *</label>
            <input type="number" name="amount" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" class="form-select" required>
                <option value="">Select Method</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="mobile">Mobile Money</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Reference No.</label>
            <input type="text" name="reference" class="form-control">
        </div>
        <button type="submit" class="btn btn-success"><i class="bi bi-cash-coin"></i> Submit Payment</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
