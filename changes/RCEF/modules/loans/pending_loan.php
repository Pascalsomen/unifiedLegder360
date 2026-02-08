<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

$loanSystem = new LoanSystem($pdo);

$filters = [
    'borrower_name' => $_GET['borrower_name'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$sql = "SELECT l.*, u.first_name as borrower_name,
        (SELECT COUNT(*) FROM loan_repayments r WHERE r.loan_id = l.id AND r.is_paid = 1) as paid_count,
        (SELECT COUNT(*) FROM loan_repayments r WHERE r.loan_id = l.id) as total_count
        FROM loans l
        JOIN borrower u ON u.id = l.borrower_id
        WHERE 1=1";

$params = [];
if ($filters['borrower_name']) {
    $sql .= " AND u.first_name LIKE ?";
    $params[] = "%" . $filters['borrower_name'] . "%";
}
if ($filters['status']) {
    $sql .= " AND l.status = ?";
    $params[] = $filters['status'];
}

$sql .= " ORDER BY l.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Pedding Loan</h2>

    <form method="get" class="row g-3 mb-3">
        <div class="col-md-4">
            <input type="text" name="borrower_name" class="form-control" placeholder="Search by Borrower" value="<?= htmlspecialchars($filters['borrower_name']) ?>">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>

                <th>Borrower</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Repayment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan): ?>
                <tr>
                    <td><?= $loan['id'] ?></td>

                    <td><?= htmlspecialchars($loan['borrower_name']) ?></td>
                    <td><?= number_format($loan['amount'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $loan['status'] === 'approved' ? 'success' : ($loan['status'] === 'rejected' ? 'danger' : 'secondary') ?>">
                            <?= ucfirst($loan['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?= $loan['paid_count'] ?> / <?= $loan['total_count'] ?>
                        <?= $loan['paid_count'] == $loan['total_count'] && $loan['total_count'] > 0 ? '<span class="badge bg-success">Completed</span>' : '' ?>
                    </td>
                    <td>
                        <a href="loan_view.php?id=<?= $loan['id'] ?>" class="btn btn-sm btn-info">View</a>
                        <a href="repayment_add.php?loan_id=<?= $loan['id'] ?>" class="btn btn-sm btn-info">Record Payment</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
