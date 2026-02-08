<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);

// Get sponsor ID from query
if (!isset($_GET['id'])) {
    echo "Sponsor ID is required.";
    exit;
}

$id = $_GET['id'];
$sponsor = $school->getSponsorById($id); // Assume this returns associative array

if (!$sponsor) {
    echo "Sponsor not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['full_name'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone']
    ];

    $school->updateSponsor($id, $data);
    $_SESSION['toast'] ="Edited Successfully";
    echo "<script>window.location ='sponsor_list.php'</script>";
    exit;
}
?>

<div class="container mt-4">
    <h4>Edit Sponsor</h4>
    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($sponsor['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($sponsor['email']) ?>">
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($sponsor['address']) ?>">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($sponsor['phone']) ?>">
        </div>

        <button type="submit" class="btn btn-primary mt-2">Update Sponsor</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
