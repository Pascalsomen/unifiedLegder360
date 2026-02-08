<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="container mt-4">
    <h2><i class="bi bi-plus-circle"></i> Apply for Loan</h2>
    <form method="post" action="process_create.php">
        <div class="mb-3">
            <label class="form-label">Loan Amount *</label>
            <input type="number" name="amount" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Interest Rate (%) *</label>
            <input type="number" name="interest_rate" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Term (in months) *</label>
            <input type="number" name="term_months" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Purpose *</label>
            <textarea name="purpose" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Submit</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>