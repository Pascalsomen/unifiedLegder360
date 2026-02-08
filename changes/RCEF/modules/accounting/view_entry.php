<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasRole('accountant')) {
    redirect('/index.php');
}

// Get entry ID from URL
$entryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($entryId <= 0) {
    $_SESSION['error'] = "Invalid journal entry ID";
    redirect('/modules/accounting/journal_entries.php');
}

// Fetch transaction header
$stmt = $pdo->prepare("SELECT t.*, u.username as created_by_name
                      FROM transactions t
                      JOIN users u ON t.created_by = u.id
                      WHERE t.id = ?");
$stmt->execute([$entryId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    $_SESSION['error'] = "Journal entry not found";
    redirect('/modules/accounting/journal_entries.php');
}

// Fetch transaction lines
$stmt = $pdo->prepare("SELECT tl.*, a.account_code, a.account_name
                      FROM transaction_lines tl
                      JOIN chart_of_accounts a ON tl.account_id = a.id
                      WHERE tl.transaction_id = ?
                      ORDER BY tl.debit DESC, tl.id");
$stmt->execute([$entryId]);
$lines = $stmt->fetchAll();

// Calculate totals
$debitTotal = 0;
$creditTotal = 0;
foreach ($lines as $line) {
    $debitTotal += $line['debit'];
    $creditTotal += $line['credit'];
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Journal Entry #<?= $transaction['id'] ?></h2>
                <div>
                    <a href="journal_entries.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Journal
                    </a>
                    <?php if (hasRole('accountant')): ?>
                        <a href="edit_entry.php?id=<?= $entryId ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                    <a  href="<?php echo  $base;?>/journal_voucher.php?id=<?= $entryId ?>" class="btn btn-primary" >
                        <i class="fas fa-print"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Entry Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <h6>Date</h6>
                            <p><?= date('F j, Y', strtotime($transaction['transaction_date'])) ?></p>
                        </div>
                        <div class="col-md-3">
                            <h6>Reference</h6>
                            <p><?= htmlspecialchars($transaction['reference']) ?></p>
                        </div>

                         <div class="col-md-3">
                            <h6>Transaction Type</h6>
                            <p><?= htmlspecialchars($transaction['trx_type']) ?></p>
                        </div>
                        <div class="col-md-12">
                            <h6>Description</h6>
                            <p><?= htmlspecialchars($transaction['description']) ?></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Account</th>
                                    <th width="30%">Account Name</th>
                                    <th width="15%">Debit</th>
                                    <th width="15%">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lines as $line): ?>
                                <tr>
                                    <td><?= htmlspecialchars($line['account_code']) ?></td>
                                    <td><?= htmlspecialchars($line['account_name']) ?></td>
                                    <td class="text-end"><?= $line['debit'] > 0 ? number_format($line['debit'], 2) : '' ?></td>
                                    <td class="text-end"><?= $line['credit'] > 0 ? number_format($line['credit'], 2) : '' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-active">
                                <tr>
                                    <th colspan="2" class="text-end">Totals</th>
                                    <th class="text-end"><?= number_format($debitTotal, 2) ?></th>
                                    <th class="text-end"><?= number_format($creditTotal, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        Created by <?= htmlspecialchars($transaction['created_by_name']) ?> on
                        <?= date('F j, Y \a\t g:i a', strtotime($transaction['created_at'])) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Entry Summary</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Status</h6>
                        <span class="badge bg-success">Posted</span>
                    </div>

                    <div class="mb-3">
                        <h6>Total Amount</h6>
                        <p><?= number_format($debitTotal, 2) ?></p>
                    </div>

                    <div class="mb-3">
                        <h6>Number of Lines</h6>
                        <p><?= count($lines) ?></p>
                    </div>

                    <div class="mb-3">
                        <h6>Balance Check</h6>
                        <?php if (abs($debitTotal - $creditTotal) < 0.01): ?>
                            <span class="badge bg-success">Balanced</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Unbalanced (Difference: <?= number_format(abs($debitTotal - $creditTotal), 2) ?>)</span>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div>
                        <h6>Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="journal_entries.php" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> View All Entries
                            </a>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>Related Entries</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php
                        // Fetch related entries (same reference or same date)
                        $stmt = $pdo->prepare("SELECT t.id, t.reference, t.transaction_date
                                              FROM transactions t
                                              WHERE (t.reference = ? OR t.transaction_date = ?)
                                              AND t.id != ?
                                              ORDER BY t.transaction_date DESC
                                              LIMIT 5");
                        $stmt->execute([$transaction['reference'], $transaction['transaction_date'], $entryId]);
                        $relatedEntries = $stmt->fetchAll();

                        if (count($relatedEntries) > 0):
                            foreach ($relatedEntries as $entry):
                        ?>
                            <a href="view_entry.php?id=<?= $entry['id'] ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($entry['reference']) ?></strong>
                                    <span><?= date('M j, Y', strtotime($entry['transaction_date'])) ?></span>
                                </div>
                            </a>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <div class="list-group-item">
                                <p class="text-muted mb-0">No related entries found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reverse Entry Modal -->
<div class="modal fade" id="reverseModal" tabindex="-1" aria-labelledby="reverseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reverseModalLabel">Reverse Journal Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reverse this journal entry?</p>
                <p>This will create a new reversing entry with the opposite amounts.</p>
                <div class="mb-3">
                    <label for="reverseDate" class="form-label">Reversal Date</label>
                    <input type="date" class="form-control" id="reverseDate" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReverse">Reverse Entry</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle reverse entry
    $('#confirmReverse').click(function() {
        const reverseDate = $('#reverseDate').val();

        if (!reverseDate) {
            alert('Please select a reversal date');
            return;
        }

        $.post('reverse_entry.php', {
            entry_id: <?= $entryId ?>,
            reverse_date: reverseDate
        }, function(response) {
            if (response.success) {
                window.location.href = 'view_entry.php?id=' + response.new_entry_id;
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Error reversing entry');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>