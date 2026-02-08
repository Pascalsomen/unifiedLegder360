<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingPeriod.php';

if (!hasRole('accountant')) {
    redirect($base);
}

$accountingPeriod = new AccountingPeriod($pdo);
$currentPeriod = $accountingPeriod->getCurrentPeriod();
$allPeriods = $accountingPeriod->getAllPeriods();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_period'])) {
        try {
            $startDate = new DateTime($_POST['start_date']);
            $endDate = new DateTime($_POST['end_date']);

            $periodId = $accountingPeriod->createPeriod(
                $_POST['name'],
                $startDate,
                $endDate,
                $_SESSION['user_id']
            );

            $_SESSION['success'] = "Accounting period created successfully!";
            redirect('/modules/accounting/accounting_periods.php');
        } catch (Exception $e) {
            $_SESSION['error'] = "Error creating period: " . $e->getMessage();
        }
    } elseif (isset($_POST['close_period'])) {
        try {
            $accountingPeriod->closePeriod(
                $_POST['period_id'],
                $_SESSION['user_id'],
                $_POST['closing_notes']
            );

            $_SESSION['toast'] = "Accounting period closed successfully!";
            redirect('/modules/accounting/accounting_periods.php');
        } catch (Exception $e) {
            $_SESSION['error'] = "Error closing period: " . $e->getMessage();
        }
    } elseif (isset($_POST['reactivate_period'])) {
        try {
            $accountingPeriod->reactivatePeriod($_POST['period_id'], $_SESSION['user_id']);

            $_SESSION['toast'] = "Accounting period reactivated successfully!";
            redirect('accounting_periods.php');
        } catch (Exception $e) {
            $_SESSION['toast'] = "Error reactivating period: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Accounting Periods</h2>
            <?php if ($currentPeriod): ?>
                <div class="alert alert-info">
                    <strong>Current Active Period:</strong>
                    <?= htmlspecialchars($currentPeriod['name']) ?>
                    (<?= date('M j, Y', strtotime($currentPeriod['start_date'])) ?> - <?= date('M j, Y', strtotime($currentPeriod['end_date'])) ?>)
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No active accounting period. Please create a new period.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Period</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Period Name</label>
                            <input type="text" class="form-control" name="name" required
                                   placeholder="e.g., FY2023 Q1, January 2023">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>

                        <?php if(hasPermission(3)): ?>
                            <button type="submit" name="create_period" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Create Period
                            </button>
                        <?php else: ?>
                            You do not have access to create Accounting Period
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Close Current Period</h4>
                </div>
                <div class="card-body">
                    <?php if ($currentPeriod): ?>
                        <form method="POST">
                            <input type="hidden" name="period_id" value="<?= $currentPeriod['id'] ?>">
                            <div class="mb-3">
                                <p>You are about to close:</p>
                                <h5><?= htmlspecialchars($currentPeriod['name']) ?></h5>
                                <p><?= date('F j, Y', strtotime($currentPeriod['start_date'])) ?> - <?= date('F j, Y', strtotime($currentPeriod['end_date'])) ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Closing Notes</label>
                                <textarea class="form-control" name="closing_notes" rows="3"></textarea>
                            </div>
                            <?php if(hasPermission(42)): ?>
                                <button type="submit" name="close_period" class="btn btn-danger">
                                    <i class="fas fa-lock"></i> Close Period
                                </button>
                            <?php else: ?>
                                You do not have access to close Accounting Period
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            No active period to close.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>All Accounting Periods</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Period Name</th>
                                    <th>Date Range</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Closed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPeriods as $period): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($period['name']) ?></td>
                                        <td>
                                            <?= date('M j, Y', strtotime($period['start_date'])) ?> -
                                            <?= date('M j, Y', strtotime($period['end_date'])) ?>
                                        </td>
                                        <td>
                                            <?php if ($period['is_closed']): ?>
                                                <span class="badge bg-secondary">Closed</span>
                                            <?php elseif ($period['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php elseif (date('Y-m-d') < $period['start_date']): ?>
                                                <span class="badge bg-info">Future</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Past (Not Closed)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($period['created_by_name']) ?><br>
                                            <small><?= date('M j, Y', strtotime($period['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($period['closed_by_name']): ?>
                                                <?= htmlspecialchars($period['closed_by_name']) ?><br>
                                                <small><?= date('M j, Y', strtotime($period['closed_at'])) ?></small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="period_report.php?id=<?= $period['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Report">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>

                                            <?php if (hasPermission(42) && $period['is_active']==0  ): ?>
                                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to reactivate this period? This will deactivate the current active period.');">
                                                    <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
                                                    <button type="submit" name="reactivate_period" class="btn btn-sm btn-outline-success" title="Reactivate Period">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
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
    </div>
</div>

<script>
$(document).ready(function() {
    // Set default dates for new period
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    const endOfNextMonth = new Date(nextMonth.getFullYear(), nextMonth.getMonth() + 1, 0);

    $('input[name="start_date"]').val(today.toISOString().split('T')[0]);
    $('input[name="end_date"]').val(endOfNextMonth.toISOString().split('T')[0]);

    // Confirm before closing period
    $('button[name="close_period"]').click(function(e) {
        if (!confirm('Are you sure you want to close this accounting period? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
