<?php
include '../../includes/header.php';

// Get selected account and date range
$accountId = $_GET['account_id'] ?? null;
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Fetch accounts
$accounts = $pdo->query("SELECT id, account_code, account_name FROM chart_of_accounts ORDER BY account_code")->fetchAll(PDO::FETCH_ASSOC);

$entries = [];
$accountName = '';

if ($accountId) {
    $stmt = $pdo->prepare("
        SELECT t.transaction_date, t.description, t.reference, t.id,tl.debit, tl.credit, a.account_name
        FROM transactions t
        JOIN transaction_lines tl ON t.id = tl.transaction_id
        JOIN chart_of_accounts a ON a.id = tl.account_id
        WHERE tl.account_id = ? AND t.transaction_date BETWEEN ? AND ?
        ORDER BY t.id ASC
    ");
    $stmt->execute([$accountId, $startDate, $endDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $accountName = $entries[0]['account_name'] ?? '';
}
?>


<div class="container mt-4">
    <h3>Account Ledger</h3>
    <form method="get" class="row mb-3">
        <div class="col-md-4">
            <select name="account_id" class="form-control" required>
                <option value="">-- Select Account --</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $accountId ? 'selected' : '' ?>>
                        <?= $acc['account_code'] ?> - <?= $acc['account_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="start_date" value="<?= $startDate ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" value="<?= $endDate ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>

    <?php if ($entries): ?>
    <div class="mb-2">
        <button class="btn btn-success" onclick="exportTableToExcel('ledgerTable', 'account_ledger')">Export to Excel</button>
        <button class="btn btn-secondary" onclick="window.print()">Print</button>
    </div>

    <table class="table table-bordered" id="ledgerTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                 <th>Reference</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Running Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $balance = 0;
        foreach ($entries as $entry):
            $balance += $entry['debit'] - $entry['credit'];
        ?>
            <tr>
                <td><?= $entry['transaction_date'] ?></td>
                <td><?= $entry['description'] ?></td>
                <td><a href="view_entry.php?id=<?= $entry['id'] ?>"> <?= $entry['reference'] ?>  </a></td>
                <td><?= number_format($entry['debit'], 2) ?></td>
                <td><?= number_format($entry['credit'], 2) ?></td>
                <td><?= number_format($balance, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
function exportTableToExcel(tableID, filename = ''){
    let downloadLink;
    const dataType = 'application/vnd.ms-excel';
    const tableSelect = document.getElementById(tableID);
    const tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    filename = filename ? filename + '.xls' : 'excel_data.xls';

    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    if(navigator.msSaveOrOpenBlob){
        const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else{
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}
</script>
<?php include '../../includes/footer.php'; ?>
