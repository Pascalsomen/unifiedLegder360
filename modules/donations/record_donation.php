<?php require_once __DIR__ . '/../../includes/header.php';
if (!hasRole('donations')) {
    redirect($base);
}
// Fetch dropdown data
$donors = $pdo->query("SELECT id, name FROM donors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$projects = $pdo->query("SELECT id, name FROM donation_projects ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="mb-4">Record New Donation</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="process_donation.php" method="POST">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Donor *</label>
                <select name="donor_id" class="form-select" required>
                    <option value="">Select Donor</option>
                    <?php foreach ($donors as $donor): ?>
                        <option value="<?= $donor['id'] ?>"><?= htmlspecialchars($donor['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Amount *</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Currency *</label>
                <input type="text" name="currency" class="form-control" value="RWF" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Date *</label>
                <input type="date" name="donation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Method *</label>
                <input type="text" name="payment_method" class="form-control" placeholder="e.g. Bank Transfer, Cash" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Receipt Number</label>
                <input type="text" name="receipt_number" class="form-control">
            </div>

        <label for="receipt" class="form-label">Upload Receipt</label>
          <input type="file" class="form-control" name="receipt" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="mb-3">
            <label class="form-label">Purpose</label>
            <textarea name="purpose" class="form-control" rows="2"></textarea>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Project (if applicable)</label>
                <select name="project_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Acknowledged?</label>
                <select name="is_acknowledged" class="form-select">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>

        <?php if(hasPermission(41)){?>
            <button type="submit" class="btn btn-primary">Save Donation</button>
                        <?php }else{
                            Echo "You do not have access to record donation";
                        } ?>


    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
