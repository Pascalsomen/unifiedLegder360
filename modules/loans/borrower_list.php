<?php
require_once __DIR__ . '/../../includes/header.php';  // Include header
require_once '../../classes/SchoolFeesSystem.php';   // Include the class where DB connection is established

$school = new SchoolFeesSystem($pdo);  // Create an instance of the SchoolFeesSystem class

// Fetch all borrowers
$stmt = $pdo->prepare("SELECT * FROM borrower");
$stmt->execute();
$borrowers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HTML Table to display borrowers -->
<div class="container mt-4">
<h2>List of Borrowers  <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Borrowers')"> Export to Excel</button>

<?php if(hasPermission(37)){?>
                  <a href="borrowers.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Borrower</a>
                        <?php }else{
                           // Echo "You do not have access to record donation";
                        } ?>
 </h2>


<table id="table" class="table table-striped">
    <thead>
        <tr>
            <th>ID Number</th>
            <th>Name</th>
            <th>Bank Account number</th>
            <th>Address</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($borrowers as $borrower): ?>
            <tr>
                <td><?php echo htmlspecialchars($borrower['id_number']); ?></td>
                <td><?php echo htmlspecialchars($borrower['first_name']) . ' ' . htmlspecialchars($borrower['last_name']); ?></td>
                <td><?php echo htmlspecialchars($borrower['account_number']); ?></td>
                <td><?php echo htmlspecialchars($borrower['address']); ?></td>
                <td><?php echo htmlspecialchars($borrower['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($borrower['email']); ?></td>
                <td>
                    <!-- Edit Button -->

                  <?php if(hasPermission(38)){?>

                  <a href="edit_borrower.php?id=<?php echo $borrower['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php }else{
                           // Echo "You do not have access to record donation";
                        } ?>

                    <!-- Delete Button -->

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
