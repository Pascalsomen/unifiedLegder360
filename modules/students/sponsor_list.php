<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/SchoolFeesSystem.php';

$schoolFees = new SchoolFeesSystem($pdo);
$sponsors = $schoolFees->getAllSponsors(); // assumes a method returning all sponsors
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Sponsor List</h2>
        <?php if(hasPermission(30)){?>

            <a href="add_sponsor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Sponsor</a>
<?php }else{
//Echo "You do not have access to add new sponsor";
} ?>

    </div>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($sponsors)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-custom ">
                        <thead >
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sponsors as $index => $sponsor): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($sponsor['name']) ?></td>
                                    <td><?= htmlspecialchars($sponsor['email']) ?></td>
                                    <td><?= htmlspecialchars($sponsor['phone']) ?></td>
                                    <td><?= htmlspecialchars($sponsor['address']) ?></td>
                                    <td><?= date('d M Y', strtotime($sponsor['created_at'])) ?></td>
                                    <td>
                                    <?php if(hasPermission(31)){?>

                                        <a href="edit_sponsor.php?id=<?= $sponsor['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
<?php }else{
//Echo "You do not have access to add new sponsor";
} ?>

<a href="view_sponsored_students.php?id=<?= $sponsor['id'] ?>" class="btn btn-sm btn-info">
    <i class="fas fa-users"></i> View Students
</a>


                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No sponsors found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
