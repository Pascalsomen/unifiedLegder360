<div class="container mt-4">
    <h2>New Loan Application</h2>
    <form id="loanForm" method="post" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Loan Type</label>
                <select name="loan_type" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="cash">Cash Loan</option>
                    <option value="asset">Asset Financing</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Borrower</label>
                <select name="borrower_id" class="form-select" required>
                    <?php foreach ($borrowers as $borrower): ?>
                        <option value="<?= $borrower['id'] ?>">
                            <?= htmlspecialchars($borrower['company_name'] ?? $borrower['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Principal Amount</label>
                <input type="number" name="principal_amount" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Interest Rate (%)</label>
                <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Term (months)</label>
                <input type="number" name="term_months" class="form-control" min="1" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Purpose</label>
            <textarea name="purpose" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Supporting Documents</label>
            <input type="file" name="documents[]" class="form-control" multiple>
            <small class="text-muted">Upload signed agreement, ID, etc.</small>
        </div>

        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>
</div>