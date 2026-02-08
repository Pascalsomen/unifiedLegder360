<div class="container mt-4">
    <h2>Loan Repayments</h2>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Loan #<?= $loan['loan_number'] ?></h5>
                <span class="badge bg-<?= $statusColors[$loan['status']] ?>">
                    <?= ucfirst($loan['status']) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Principal:</strong> <?= number_format($loan['principal_amount'], 2) ?>
                </div>
                <div class="col-md-4">
                    <strong>Interest Rate:</strong> <?= $loan['interest_rate'] ?>%
                </div>
                <div class="col-md-4">
                    <strong>Term:</strong> <?= $loan['term_months'] ?> months
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($repayments as $repayment): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($repayment['due_date'])) ?></td>
                            <td><?= number_format($repayment['amount_due'], 2) ?></td>
                            <td><?= number_format($repayment['amount_paid'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $paymentStatusColors[$repayment['status']] ?>">
                                    <?= ucfirst($repayment['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $repayment['payment_date'] ? date('M j, Y', strtotime($repayment['payment_date'])) : '--' ?>
                            </td>
                            <td>
                                <?php if ($repayment['status'] !== 'paid'): ?>
                                <button class="btn btn-sm btn-primary record-payment"
                                        data-repayment-id="<?= $repayment['id'] ?>">
                                    Record Payment
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="paymentForm" method="post">
                <input type="hidden" name="repayment_id" id="modalRepaymentId">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference/Receipt No.</label>
                        <input type="text" name="reference" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>