<?php
include '../../includes/header.php';

if (!hasRole('accountant')) {
    redirect($base);
}
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Fetch general ledger entries
$stmt = $pdo->prepare("
    SELECT
        t.transaction_date,
          t.reference,
           t.id,
        t.description,
        a.account_code,
        a.account_name,
        tl.debit,
        tl.credit
    FROM transactions t
    JOIN transaction_lines tl ON t.id = tl.transaction_id
    JOIN chart_of_accounts a ON a.id = tl.account_id
    WHERE t.transaction_date BETWEEN ? AND ?
    ORDER BY a.account_code, t.transaction_date
");
$stmt->execute([$startDate, $endDate]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>General Ledger  <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Trial Balance')">Export to Excel</button></h3>
    <form method="get" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <input type="date" name="start_date" value="<?= $startDate ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" value="<?= $endDate ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <table id="table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Account</th>
                <th>Transaction Date</th>
                <th>Transaction code</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
        <?php $no=0; foreach ($entries as $entry): ?>
            <tr>
                  <td><?php echo $no= $no + 1;?></td>
                <td><?= $entry['account_code'] ?> - <?= $entry['account_name'] ?></td>
                <td><?= $entry['transaction_date'] ?></td>
              <td><a href="view_entry.php?id=<?= $entry['id'] ?>"> <?= $entry['reference'] ?>  </a></td>
                <td><?= $entry['description'] ?></td>
                <td><?= number_format($entry['debit'], 2) ?></td>
                <td><?= number_format($entry['credit'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>