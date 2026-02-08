<?php include '../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo); ?>
<div class="container mt-4">
    <h4>Academic Terms</h4>

    <?php if(hasPermission(33)){?>

        <a href="add_term.php" class="btn btn-success mb-3">+ Add Term</a>
<?php }else{
//Echo "You do not have access to add new sponsor";
} ?>

    <table class="table table-bordered">
        <thead>
            <tr><th>Term Name</th><th>Year</th><th>Start Date</th><th>End Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($school->getTerms() as $term): ?>
                <tr>
                    <td><?= $term['term_name'] ?></td>
                    <td><?= $term['year'] ?></td>
                    <td><?= $term['start_date'] ?></td>
                    <td><?= $term['end_date'] ?></td>
                    <td>

                    <?php if(hasPermission(47)){?>

<a href="edit_term.php?id=<?= $term['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
<?php }else{
//Echo "You do not have access to add new sponsor";
} ?>

                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
