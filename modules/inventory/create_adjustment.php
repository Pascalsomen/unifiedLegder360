<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Inventory.php';

if (!hasPermission('manage_inventory')) {
    redirect('/index.php');
}

$inventorySystem = new Inventory($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $adjustmentHeader = [
            'adjustment_date' => $_POST['adjustment_date'],
            'reference' => $_POST['reference'],
            'reason' => $_POST['reason']
        ];

        $adjustmentItems = [];
        foreach ($_POST['items'] as $item) {
            if (!empty($item['item_id']) && isset($item['quantity_change'])) {
                $adjustmentItems[] = [
                    'item_id' => $item['item_id'],
                    'quantity_change' => $item['quantity_change'],
                    'notes' => $item['notes'] ?? null,
                    'location_id' => $item['location_id'] ?? null
                ];
            }
        }

        if (empty($adjustmentItems)) {
            throw new Exception("At least one item is required for adjustment");
        }

        $adjustmentId = $inventorySystem->createAdjustment(
            $adjustmentHeader,
            $adjustmentItems,
            $_SESSION['user_id']
        );

        $_SESSION['success'] = "Inventory adjustment created successfully!";
        redirect("/modules/inventory/view_adjustment.php?id=$adjustmentId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get active inventory items
$items = $inventorySystem->getActiveItems();
$locations = $inventorySystem->getLocations();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Create Inventory Adjustment</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Adjustment Date*</label>
                        <input type="date" class="form-control" name="adjustment_date"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference</label>
                        <input type="text" class="form-control" name="reference"
                               placeholder="ADJ-<?= date('Ymd') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reason*</label>
                        <input type="text" class="form-control" name="reason" required>
                    </div>
                </div>

                <h5 class="mt-4">Adjustment Items</h5>
                <div class="table-responsive mb-3">
                    <table class="table" id="adjustmentItems">
                        <thead>
                            <tr>
                                <th width="30%">Item*</th>
                                <th width="15%">Current Stock</th>
                                <th width="15%">Adjustment*</th>
                                <th width="15%">New Qty</th>
                                <th width="15%">Location</th>
                                <th width="20%">Notes</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items will be added here -->
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" id="addItemBtn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const items = <?= json_encode($items) ?>;
    const locations = <?= json_encode($locations) ?>;
    let itemCounter = 0;

    // Function to add a new item row
    function addItemRow(item = null) {
        const rowId = `item-${itemCounter++}`;
        const itemId = item ? item.id : '';
        const itemName = item ? `${item.item_code} - ${item.item_name}` : '';
        const currentQty = item ? item.quantity_on_hand : 0;

        const newRow = `
            <tr id="${rowId}">
                               <td>
                    <select class="form-select item-select" name="items[${itemCounter}][item_id]" required>
                        <option value="">Select Item</option>
                        ${items.map(i =>
                            `<option value="${i.id}" ${itemId === i.id ? 'selected' : ''}>
                                ${i.item_code} - ${i.item_name}
                            </option>`
                        ).join('')}
                    </select>
                </td>
                <td class="current-qty">${currentQty}</td>
                <td>
                    <input type="number" class="form-control qty-change"
                           name="items[${itemCounter}][quantity_change]" required
                           step="0.01" placeholder="0.00">
                </td>
                <td class="new-qty">${currentQty}</td>
                <td>
                    <select class="form-select" name="items[${itemCounter}][location_id]">
                        <option value="">Select Location</option>
                        ${locations.map(l =>
                            `<option value="${l.id}">${l.location_name}</option>`
                        ).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control"
                           name="items[${itemCounter}][notes]" placeholder="Notes">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#adjustmentItems tbody').append(newRow);

        // Add event listeners for the new row
        $(`#${rowId} .item-select`).change(function() {
            const selectedId = $(this).val();
            const selectedItem = items.find(i => i.id == selectedId);
            if (selectedItem) {
                $(this).closest('tr').find('.current-qty').text(selectedItem.quantity_on_hand);
                updateNewQty($(this).closest('tr'));
            }
        });

        $(`#${rowId} .qty-change`).on('input', function() {
            updateNewQty($(this).closest('tr'));
        });
    }

    // Function to update the new quantity display
    function updateNewQty(row) {
        const currentQty = parseFloat(row.find('.current-qty').text()) || 0;
        const qtyChange = parseFloat(row.find('.qty-change').val()) || 0;
        row.find('.new-qty').text(currentQty + qtyChange);
    }

    // Add initial empty row
    addItemRow();

    // Add item button click handler
    $('#addItemBtn').click(function() {
        addItemRow();
    });

    // Remove item button click handler (using event delegation)
    $('#adjustmentItems').on('click', '.remove-item', function() {
        if ($('#adjustmentItems tbody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('You must have at least one item.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>