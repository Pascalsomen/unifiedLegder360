<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasRole('donations')) {
    redirect($base);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $categoryId = $_POST['category_id'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    $taxId = $_POST['tax_id'] ?? null;
    try {
        $stmt = $pdo->prepare("UPDATE donors SET  name='$name', type='$type', category_id='$categoryId', email='$email', phone='$phone', address='$address',tax_id='$taxId' WHERE  id='".$_REQUEST['id']."'");

        $stmt->execute();

        $_SESSION['success'] = "Donor updated successfully!";
       echo "<script>window.location='donors.php'</script>";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding donor: " . $e->getMessage();
    }
}

// Fetch existing data
$donors = $pdo->query("SELECT d.*, c.name as category_name
                       FROM donors d
                       LEFT JOIN donor_categories c ON d.category_id = c.id
                       where d.id='".$_REQUEST['id']."' ORDER BY d.name")->fetchAll();
$categories = $pdo->query("SELECT * FROM donor_categories ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Update Donor</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                    <?php foreach ($donors as $donor): ?>
                        <div class="form-group">
                            <label>Donor Name*</label>
                            <input type="text" class="form-control" value='<?= $donor['name'] ?>' name="name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Donor Type*</label>
                                    <select class="form-control" name="type" required>

                                    <option value='<?= $donor['type'] ?>'><?= $donor['type'] ?></option>
                                        <option value="individual">Individual</option>
                                        <option value="organization">Organization</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Donor Category</label>
                                    <select class="form-control" name="category_id">
                                        <option value="<?= $donor['category_id'] ?>">-- Select Category --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email"  value='<?= $donor['email'] ?>' class="form-control" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text"  value='<?= $donor['phone'] ?>' class="form-control" name="phone">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control"  value='<?= $donor['address'] ?>' name="address" rows="2"><?= $donor['address'] ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Additional Info</label>
                            <input type="text" class="form-control" value='<?= $donor['tax_id'] ?>'  name="tax_id">
                        </div>
<br>

<?php if(hasPermission(44)){?>
                   <button type="submit" class="btn btn-primary">Update Donor</button>
                        <?php }else{
                            Echo "You do not have access to edit donor";
                        } ?>

                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>