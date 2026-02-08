<?php require_once __DIR__ . '/../..//includes/header.php';
require_once __DIR__ . '/../..//classes/InventorySystem.php';

if (!hasRole('inventory')) {
    redirect($base);
}

$inventorySystem = new InventorySystem($pdo);

// Handle Add, Edit, or Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        // Handle item creation
        $itemData = [
            'item_name' => $_POST['item_name'],
            'description' => $_POST['description'],
            'itemtype' => $_POST['itemtype'],
            'unit' => $_POST['unit']
        ];
        $inventorySystem->addItem($itemData);
        $_SESSION['toast'] = "Item Successfully Added";
        echo "<script>window.location='stock_items.php'</script>";
        exit;
    } elseif (isset($_POST['edit_item'])) {
        // Handle item update
        $itemId = $_POST['item_id'];
        $itemData = [
            'item_name' => $_POST['item_name'],
            'description' => $_POST['description'],
            'itemtype' => $_POST['itemtype'],
            'unit' => $_POST['unit']
        ];
        $inventorySystem->updateItem($itemId, $itemData);
        echo $_SESSION['toast'] = "Item Successfully Updated";
        echo "<script>window.location='stock_items.php'</script>";
        exit;
    }
} elseif (isset($_GET['delete'])) {
    // Handle item deletion
    $itemId = $_GET['delete'];
    $inventorySystem->deleteItem($itemId);
    $_SESSION['toast'] = "Item Successfully Deleted";
    echo "<script>window.location='stock_items.php'</script>";
    exit;
}

// Fetch all stock items
$stockItems = $inventorySystem->listItems();
?>

<div class="container mt-5">
    <h3 class="mb-4">Stock Items</h3>

    <?php if(hasPermission(4)){
                            ?>
    <!-- Add Item Form (for adding new stock items) -->
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addItemModal">Add New Item</button>

    <?php }else{
        echo 'You do not have access to create an item';
    }?>

    <!-- Stock Items Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Item Type</th>
                <th>Unit</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($stockItems as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['itemtype']) ?></td>
                    <td><?= htmlspecialchars($item['unit']) ?></td>
                    <td>
                        <!-- Edit and Delete Actions -->
<?php
if (hasPermission(5)){
?> <a href="stock_items.php?edit=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
<?php } ?>

<?php
if(hasPermission(6)){?>
<a href="stock_items.php?delete=<?= $item['id'] ?>" disabled class="btn btn-danger btn-sm">Delete</a>
<?php } ?>


                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Stock Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>

                     <div class="mb-3">
                        <label for="description" class="form-label">Item Type</label>
                        <select class="form-label" name="itemtype" required>
                             <option value="">Select Item Type</option>
                             <option value="inventory">Inventory Item</option>
                             <option value="service">Service Item</option>
                             <option value="fixed">Fixed Asset Item</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit" required>
                    </div>
                    <button type="submit" name="add_item" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal (For editing existing stock items) -->
<?php if (isset($_GET['edit'])): ?>
    <?php
    $itemId = $_GET['edit'];
    $item = $inventorySystem->getItem($itemId);
    ?>
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Stock Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($item['description']) ?>" required>
                        </div>

                            <div class="mb-3">
                        <label for="description" class="form-label">Item Type</label>
                        <select class="form-label" name="itemtype" required>
                            <?php $item['itemtype'] ?>
                             <option value="">Select Item Type </option>
                             <option value="invetory" <?= $item['itemtype'] == 'invetory' ? 'selected' : '' ?>>Invetory Item</option>
                             <option value="service" <?= $item['itemtype'] == 'service' ? 'selected' : '' ?>>Service Item</option>
                             <option value="fixed"  <?= $item['itemtype'] == 'fixed' ? 'selected' : '' ?>>Fixed Asset Item</option>
                        </select>
                    </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" value="<?= htmlspecialchars($item['unit']) ?>" required>
                        </div>
                        <button type="submit" name="edit_item" class="btn btn-warning">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Trigger modal on page load -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            editModal.show();
        });
    </script>
<?php endif; ?>


<?php
require_once __DIR__ . '/../..//includes/footer.php';?>
<script>
       $(document).ready(function() {
                // Initialize Select2
              $('select').select2('destroy');
             // alert('he');

       });
    </script>