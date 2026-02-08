<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/RequisitionSystem.php';
require_once __DIR__ . '/../../classes/Department.php';
require_once __DIR__ . '/../../classes/Inventory.php';

if (!hasPermission('create_requisition')) {
    redirect('/index.php');
}

$requisitionSystem = new RequisitionSystem($pdo);
$departmentSystem = new Department($pdo);
$inventorySystem = new Inventory($pdo);

// Get departments for dropdown
$departments = $departmentSystem->getAllDepartments();

// Get inventory items for dropdown
$inventoryItems = $inventorySystem->getActiveItems();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $header = [
            'department_id' => $_POST['department_id'],
            'needed_by_date' => $_POST['needed_by_date'],
            'purpose' => $_POST['purpose']
        ];

        $items = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['item_id']) {
                $items[] = [
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_of_measure' => $item['unit_of_measure'],
                    'purpose' => $item['purpose'] ?? ''
                ];
            }
        }

        $requisitionId = $requisitionSystem->createRequisition($header, $items, $_SESSION['user_id']);

        if (isset($_POST['submit_for_approval'])) {
            $requisitionSystem->submitForApproval($requisitionId, $_SESSION['user_id']);
            $_SESSION['success'] = "Requisition submitted for approval successfully!";
        } else {
            $_SESSION['success'] = "Requisition saved as draft successfully!";
        }

        redirect("/modules/inventory/view_requisition.php?id=$requisitionId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Create Internal Requisition</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Requisition Details</h4>
        </div>
        <div class="card-body">
            <form id="requisitionForm" method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Department*</label>
                        <select class="form-select" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= isset($_POST['department_id']) && $_POST['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Needed By Date*</label>
                        <input type="date" class="form-control" name="needed_by_date"
                               value="<?= $_POST['needed_by_date'] ?? date('Y-m-d', strtotime('+1 week')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Purpose*</label>
                    <textarea class="form-control" name="purpose" rows="2" required><?= $_POST['purpose'] ?? '' ?></textarea>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Requested Items</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="requisitionItems">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Item*</th>
                                    <th width="15%">Quantity*</th>
                                    <th width="15%">UOM</th>
                                    <th width="25%">Purpose</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="save_draft" class="btn btn-secondary">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" name="submit_for_approval" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let itemCounter = 0;
    const inventoryItems = <?= json_encode($inventoryItems) ?>;

    // Function to add a new item row
    function addItemRow(item = null) {
        const rowId = `item-${itemCounter++}`;
        const itemId = item ? item.id : '';
        const itemName = item ? `${item.item_code} - ${item.item_name}` : '';
        const uom = item ? item.unit_of_measure : '';

        const newRow = `
            <tr id="${rowId}">
                <td>
                    <select class="form-select item-select" name="items[${rowId}][item_id]" required>
                        <option value="">Select Item</option>
                        ${inventoryItems.map(i => `
                            <option value="${i.id}"
                                ${itemId == i.id ? 'selected' : ''}
                                data-uom="${i.unit_of_measure}">
                                ${i.item_code} - ${i.item_name} (${i.quantity_on_hand} ${i.unit_of_measure} available)
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${rowId}][quantity]"
                           step="0.01" min="0.01" value="${item ? item.quantity : ''}" required>
                </td>
                <td>
                    <input type="text" class="form-control uom-display"
                           name="items[${rowId}][unit_of_measure]" value="${uom}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="items[${rowId}][purpose]"
                           value="${item ? item.purpose : ''}">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#requisitionItems tbody').append(newRow);
        return rowId;
    }

    // Add initial item row
    addItemRow();

    // Add item button click handler
    $('#addItemBtn').click(function() {
        addItemRow();
    });

    // Remove item button click handler
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
    });

    // Item selection change handler
    $(document).on('change', '.item-select', function() {
        const selectedOption = $(this).find('option:selected');
        const uom = selectedOption.data('uom');
        $(this).closest('tr').find('.uom-display').val(uom);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>