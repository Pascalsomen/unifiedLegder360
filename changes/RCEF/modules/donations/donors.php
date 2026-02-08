<?php require_once __DIR__ . '/../../includes/header.php';

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
        $stmt = $pdo->prepare("INSERT INTO donors
                              (name, type, category_id, email, phone, address, tax_id)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $categoryId, $email, $phone, $address, $taxId]);

        $_SESSION['toast'] = "✅ Donor successful added.";

    //echo "<script>window.location='record_donation.php'</script>";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding donor: " . $e->getMessage();
    }


}

// Fetch existing data
$donors = $pdo->query("SELECT d.*, c.name as category_name
                       FROM donors d
                       LEFT JOIN donor_categories c ON d.category_id = c.id
                       ORDER BY d.name")->fetchAll();
$categories = $pdo->query("SELECT * FROM donor_categories ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Donor</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Donor Name*</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Donor Type*</label>
                                    <select class="form-control" name="type" required>
                                        <option value="individual">Individual</option>
                                        <option value="organization">Organization</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Donor Category</label>
                                    <select class="form-control" name="category_id">
                                        <option value="">-- Select Category --</option>
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
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Additional Info</label>
                            <input type="text" class="form-control" name="tax_id">
                        </div>
<br>
<?php if(hasPermission(40)){?>
                        <button type="submit" class="btn btn-primary">Save Donor</button>
                        <?php }else{
                            Echo "You do not have access to add donor";
                        } ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h4>Donor List <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Donors')">Export to Excel</button></h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-striped datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td><?= $donor['name'] ?></td>
                                    <td><?= ucfirst($donor['type']) ?></td>
                                    <td><?= $donor['category_name'] ?? 'N/A' ?></td>
                                    <td>
                                        <?= $donor['email'] ?><br>
                                        <?= $donor['phone'] ?>
                                    </td>
                                    <td>

                        <?php if(hasPermission(44)){?>
                            <a href="edit_donor.php?id=<?= $donor['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                        <?php }else{
                            //Echo "You do not have access to add donor";
                        } ?>


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



<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
