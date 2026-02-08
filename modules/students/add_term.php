<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Add Academic Term / Quarter</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form method="POST" action="process_add_term.php">
                <div class="mb-3">
                    <label for="term_name" class="form-label">Term Name *</label>
                    <input type="text" name="term_name" class="form-control" required placeholder="e.g. First Quarter">
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Year *</label>
                    <input type="text" name="year" class="form-control" required placeholder="e.g. 2024-2025">
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date *</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <?php if(hasPermission(33)){?>

                    <button type="submit" class="btn btn-primary" name="add_term"><i class="fas fa-save"></i> Save Term</button>
<?php }else{
Echo "You do not have access to add new term";
} ?>


            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
