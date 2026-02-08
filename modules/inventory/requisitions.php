<?php
// Include the necessary files
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';
$inventorySystem = new InventorySystem($pdo);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_requisition'])) {
        $requisitionData = [
            'purpose' => $_POST['purpose'],
            'status' => 'draft',
        ];
        $items = $_POST['items']; // array of item_id => quantity
        $inventorySystem->createRequisition($requisitionData, $items);
        echo "<script>window.location='requisitions.php'</script>";
        exit;
    } elseif (isset($_POST['edit_requisition'])) {
        $requisitionId = $_POST['requisition_id'];
        $requisitionData = [
            'purpose' => $_POST['purpose'],
            'status' => $_POST['status'],
        ];
        $items = $_POST['items']; // array of item_id => quantity
        $inventorySystem->updateRequisition($requisitionId, $requisitionData, $items);
        echo "<script>window.location='requisitions.php'</script>";

        exit;
    }
} elseif (isset($_GET['delete'])) {
    $requisitionId = $_GET['delete'];
    $inventorySystem->deleteRequisition($requisitionId);
    echo "<script>window.location='requisitions.php'</script>";
    exit;
} elseif (isset($_GET['approve'])) {
    $requisitionId = $_GET['approve'];
    $inventorySystem->approveRequisition($requisitionId);
    echo "<script>window.location='requisitions.php'</script>";
    exit;
}

// Fetch all requisitions
$requisitions = $inventorySystem->listRequisitions();
$stockItems = $inventorySystem->listItems();
?>

<div class="container mt-5">
    <h3 class="mb-4">Requisitions</h3>

    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addRequisitionModal">Add New Requisition</button>

    <table class="table table-bordered">
        <thead>
            <tr>
            <th>Req Number</th>
                <th>Purpose</th>
                <th>Items</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requisitions as $requisition): ?>
                <tr>
                <td>RCF-REQ-<?= htmlspecialchars($requisition['id']) ?></td>
                    <td><?= htmlspecialchars($requisition['purpose']) ?></td>
                    <td>
                        <ul>
                            <?php foreach ($requisition['items'] as $item): ?>
                                <li><?= htmlspecialchars($item['item_name']) ?> (Qty: <?= htmlspecialchars($item['quantity']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td><?= htmlspecialchars($requisition['status']) ?></td>
                    <td>
                        <?php if ($requisition['status'] == 'draft'): ?>
                            <?php if(hasPermission(14)){ ?>
                                <a href="requisitions.php?edit=<?= $requisition['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

 <?php }?>

    <a href="requisitions.php?delete=<?= $requisition['id'] ?>" class="btn btn-danger btn-sm">Delete</a>




                        <?php endif; ?>
                        <?php if ($requisition['status'] == 'submitted'): ?>


<?php if(hasPermission(13)){ ?>
    <a href="requisitions.php?approve=<?= $requisition['id'] ?>" class="btn btn-success btn-sm">Approve</a>

 <?php }?>


                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Requisition Modal -->
<div class="modal fade" id="addRequisitionModal" tabindex="-1" aria-labelledby="addRequisitionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRequisitionModalLabel">Add New Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input type="text" class="form-control" id="purpose" name="purpose" required>
                    </div>

                    <div id="itemsContainer">
                        <div class="row mb-2 itemRow">
                            <div class="col-md-7">
                                <select class="form-select" name="items[item_id][]" required>
                                    <option value="">Select item</option>
                                    <?php foreach ($stockItems as $item): ?>
                                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="items[quantity][]" placeholder="Qty" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger removeItem">X</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addItemRow" class="btn btn-secondary mb-3">+ Add Item</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_requisition" class="btn btn-primary">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Requisition Modal -->
<?php if (isset($_GET['edit'])): ?>
    <?php
    $requisitionId = $_GET['edit'];
    $requisition = $inventorySystem->getRequisition($requisitionId);
    ?>
    <div class="modal fade show" id="editRequisitionModal" style="display:block;" tabindex="-1" aria-labelledby="editRequisitionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Requisition</h5>
                        <a href="requisitions.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="requisition_id" value="<?= $requisition['id'] ?>">
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <input type="text" class="form-control" name="purpose" value="<?= htmlspecialchars($requisition['purpose']) ?>" required>
                        </div>

                        <div id="itemsContainer">
                            <?php foreach ($requisition['items'] as $item): ?>
                                <div class="row mb-2 itemRow">
                                    <div class="col-md-7">
                                        <select class="form-select" name="items[item_id][]" required>
                                            <option value="">Select item</option>
                                            <?php foreach ($stockItems as $stockItem): ?>
                                                <option value="<?= $stockItem['id'] ?>" <?= $stockItem['id'] == $item['item_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($stockItem['item_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="items[quantity][]" value="<?= htmlspecialchars($item['quantity']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger removeItem">X</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" id="addItemRow" class="btn btn-secondary mb-3">+ Add Item</button>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="draft" <?= $requisition['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="submitted" <?= $requisition['status'] == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_requisition" class="btn btn-warning">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Add item row
document.getElementById('addItemRow').addEventListener('click', function() {
    var container = document.getElementById('itemsContainer');
    var newRow = document.querySelector('.itemRow').cloneNode(true);
    newRow.querySelectorAll('input, select').forEach(function(input) {
        input.value = '';
    });
    container.appendChild(newRow);
});

// Remove item row
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeItem')) {
        e.target.closest('.itemRow').remove();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
