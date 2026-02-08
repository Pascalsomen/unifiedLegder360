<?php require_once __DIR__ . '/../../includes/header.php';


// Fetch term to edit
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid request: Term ID is required.</div>";
    exit;
}

$term_id = $_GET['id'];

// Fetch existing term data
$stmt = $pdo->prepare("SELECT * FROM terms WHERE id = ?");
$stmt->execute([$term_id]);
$term = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$term) {
    echo "<div class='alert alert-danger'>Term not found.</div>";
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Edit Academic Term / Quarter</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form method="POST" action="process_edit_term.php">
                <input type="hidden" name="term_id" value="<?= $term['id'] ?>">

                <div class="mb-3">
                    <label for="term_name" class="form-label">Term Name *</label>
                    <input type="text" name="term_name" class="form-control" value="<?= htmlspecialchars($term['term_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Year *</label>
                    <input type="text" name="year" class="form-control" value="<?= htmlspecialchars($term['year']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $term['start_date'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date *</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $term['end_date'] ?>" required>
                </div>

                <?php if (hasPermission(34)) { // Example permission ID for edit ?>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Term</button>
                <?php } else {
                    echo "<div class='alert alert-warning'>You do not have access to edit terms.</div>";
                } ?>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
