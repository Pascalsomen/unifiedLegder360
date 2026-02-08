<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/RequisitionSystem.php';
require_once __DIR__ . '/../../classes/Inventory.php';



$requisitionSystem = new RequisitionSystem($pdo);
$inventorySystem = new Inventory($pdo);
$userId = $_SESSION['user_id'];
$requisitionId = $_GET['id'] ?? 0;

// Fetch requisition details
$requisition = $requisitionSystem->getRequisitionDetails($requisitionId);
$items = $requisitionSystem->getRequisitionItems($requisitionId);

// Verify ownership and editable status
if (empty($requisition)) {
    $_SESSION['error'] = "Requisition not found";
    redirect($base_url.'/requisitions');
}

if ($requisition['requester_id'] != $userId || $requisition['status'] != 'draft') {
    $_SESSION['error'] = "You can only edit draft requisitions that you created";
    redirect($base_url."/requisitions/view_requisition.php?id=$requisitionId");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $header = [
            'department_id' => (int)$_POST['department_id'],
            'needed_by_date' => $_POST['needed_by_date'],
            'purpose' => $_POST['purpose']
        ];

        $items = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['item_id'])) {
                $items[] = [
                    'item_id' => (int)$item['item_id'],
                    'quantity' => (float)$item['quantity'],
                    'unit_of_measure' => $item['unit_of_measure'],
                    'purpose' => $item['purpose'] ?? ''
                ];
            }
        }

        // Update requisition
        $requisitionSystem->updateRequisition($requisitionId, $header, $items, $userId);

        $_SESSION['success'] = "Requisition updated successfully";
        redirect("$base_url./requisitions/view_requisition.php?id=$requisitionId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get data for dropdowns
$departments = $pdo->query("SELECT id, name FROM departments WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
$inventoryItems = $inventorySystem->getActiveItems();
$units = $pdo->query("SELECT DISTINCT unit_of_measure FROM inventory_items")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-pencil-square"></i> Edit Requisition: <?= htmlspecialchars($requisition['requisition_number']) ?>
                </h2>
                <div>
                    <a href="/modules/requisitions/view_requisition.php?id=<?= $requisitionId ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" form="editForm" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Requisition Details</h5>
                </div>
                <div class="card-body">
                    <form id="editForm" method="post">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department *</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"
                                            <?= $dept['id'] == $requisition['department_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Needed By Date *</label>
                                <input type="date" name="needed_by_date" class="form-control"
                                       value="<?= htmlspecialchars($requisition['needed_by_date']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Draft" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Purpose *</label>
                            <textarea name="purpose" class="form-control" rows="3" required><?=
                                htmlspecialchars($requisition['purpose'])
                            ?></textarea>
                        </div>

                        <hr>

                        <h5 class="mb-3">Requisition Items</h5>

                        <div class="table-responsive mb-3">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th width="30%">Item *</th>
                                        <th width="15%">Current Stock</th>
                                        <th width="15%">Quantity *</th>
                                        <th width="15%">Unit</th>
                                        <th width="20%">Purpose</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item): ?>
                                        <tr>
                                            <td>
                                                <select name="items[<?= $index ?>][item_id]" class="form-select item-select" required>
                                                    <option value="">Select Item</option>
                                                    <?php foreach ($inventoryItems as $invItem): ?>
                                                        <option value="<?= $invItem['id'] ?>"
                                                            data-stock="<?= $invItem['current_quantity'] ?>"
                                                            data-unit="<?= htmlspecialchars($invItem['unit_of_measure']) ?>"
                                                            <?= $invItem['id'] == $item['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($invItem['item_code']) ?> -
                                                            <?= htmlspecialchars($invItem['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="stock-cell">
                                                <?= $item['current_stock'] ?? 0 ?>
                                            </td>
                                            <td>
                                                <input type="number" name="items[<?= $index ?>][quantity]"
                                                       class="form-control quantity" min="0.01" step="0.01"
                                                       value="<?= htmlspecialchars($item['quantity']) ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[<?= $index ?>][unit_of_measure]"
                                                       class="form-control unit"
                                                       value="<?= htmlspecialchars($item['unit_of_measure']) ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="items[<?= $index ?>][purpose]"
                                                       class="form-control"
                                                       value="<?= htmlspecialchars($item['purpose'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" id="addItemBtn" class="btn btn-secondary">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>

                            <div>
                                <button type="submit" name="save_draft" class="btn btn-primary me-2">
                                    <i class="bi bi-save"></i> Save Draft
                                </button>
                                <button type="submit" name="submit_requisition" class="btn btn-success">
                                    <i class="bi bi-send-check"></i> Submit for Approval
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new item row
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const inventoryItems = <?= json_encode($inventoryItems) ?>;

    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        const newIndex = itemsTable.rows.length;

        newRow.innerHTML = `
            <td>
                <select name="items[${newIndex}][item_id]" class="form-select item-select" required>
                    <option value="">Select Item</option>
                    ${inventoryItems.map(item => `
                        <option value="${item.id}"
                            data-stock="${item.quantity_on_hand}"
                            data-unit="${item.unit_of_measure}">
                            ${item.item_code} - ${item.item_name}
                        </option>
                    `).join('')}
                </select>
            </td>
            <td class="stock-cell">0</td>
            <td>
                <input type="number" name="items[${newIndex}][quantity]"
                       class="form-control quantity" min="0.01" step="0.01" required>
            </td>
            <td>
                <input type="text" name="items[${newIndex}][unit_of_measure]"
                       class="form-control unit" readonly>
            </td>
            <td>
                <input type="text" name="items[${newIndex}][purpose]"
                       class="form-control">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        itemsTable.appendChild(newRow);
        initRowEvents(newRow);
    });

    // Remove item row
    function initRowEvents(row) {
        const itemSelect = row.querySelector('.item-select');
        const stockCell = row.querySelector('.stock-cell');
        const unitInput = row.querySelector('.unit');
        const removeBtn = row.querySelector('.remove-item');

        // Item selection change
        itemSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                stockCell.textContent = selectedOption.dataset.stock;
                unitInput.value = selectedOption.dataset.unit;
            } else {
                stockCell.textContent = '0';
                unitInput.value = '';
            }
        });

        // Remove row
        removeBtn.addEventListener('click', function() {
            if (itemsTable.rows.length > 1) {
                row.remove();
                reindexRows();
            } else {
                alert('You must have at least one item');
            }
        });
    }

    // Reindex rows after deletion
    function reindexRows() {
        Array.from(itemsTable.rows).forEach((row, index) => {
            // Update all input names with new index
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                input.name = name;
            });
        });
    }

    // Initialize existing rows
    Array.from(itemsTable.rows).forEach(row => {
        initRowEvents(row);
    });

    // Form submission handling
    const editForm = document.getElementById('editForm');
    editForm.addEventListener('submit', function(e) {
        // Validate at least one item
        if (itemsTable.rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the requisition');
            return;
        }

        // Additional validation can be added here
    });

    // If submit for approval button was clicked
    const submitBtn = document.querySelector('button[name="submit_requisition"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!confirm('Are you ready to submit this requisition for approval? You won\'t be able to edit it after submission.')) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                }, { once: true });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>