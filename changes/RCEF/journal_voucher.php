<?php
require_once __DIR__ . '/config/database.php';


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


$debitTotal = 0;
$creditTotal = 0;
foreach ($lines as $line) {
    $debitTotal += $line['debit'];
    $creditTotal += $line['credit'];
}

$pageTitle = "Payment Voucher #";


ob_start();
?>
  <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <h6>Date</h6>
                            <p><?= date('F j, Y', strtotime($transaction['transaction_date'])) ?></p>
                            <h6>Reference</h6>
                            <p><?= htmlspecialchars($transaction['reference']) ?></p>
                        </div>

                        <div class="col-md-6">
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

    <br><br>
    <div style="margin-top: 10px;">
        <p>___________________________</p>
        <p>Authorized Signature</p>
    </div>
<?php
$content = ob_get_clean();

// Include the reusable voucher template
require_once 'voucher_template.php';

?>