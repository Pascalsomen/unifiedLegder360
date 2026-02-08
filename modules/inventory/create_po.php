<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/PurchaseOrder.php';

// Check permissions
if (!hasPermission('inventory')) {
    redirect('/index.php');
}

$poSystem = new PurchaseOrder($pdo);
$suppliers = $pdo->query("SELECT id, name FROM suppliers WHERE is_active = 1 ORDER BY name")->fetchAll();
$items = $pdo->query("SELECT id, item_code, name, cost_price FROM inventory_items ORDER BY name")->fetchAll();
$taxRates = $pdo->query("SELECT id, name, rate FROM tax_rates  ORDER BY name")->fetchAll();

// Initialize variables
$errors = [];
$poData = [
    'supplier_id' => '',
    'order_date' => date('Y-m-d'),
    'expected_delivery_date' => '',
    'notes' => '',
    'terms' => '',
    'items' => []
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $poData['supplier_id'] = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
    $poData['order_date'] = $_POST['order_date'] ?? date('Y-m-d');
    $poData['expected_delivery_date'] = $_POST['expected_delivery_date'] ?? null;
    $poData['notes'] = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    $poData['terms'] = filter_input(INPUT_POST, 'terms', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($poData['supplier_id'])) {
        $errors['supplier_id'] = 'Supplier is required';
    }

    // Process items
    if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
        foreach ($_POST['item_id'] as $index => $itemId) {
            if (!empty($itemId)) {
                $quantity = filter_var($_POST['quantity'][$index], FILTER_VALIDATE_FLOAT);
                $price = filter_var($_POST['price'][$index], FILTER_VALIDATE_FLOAT);
                $taxRateId = filter_var($_POST['tax_rate_id'][$index], FILTER_VALIDATE_INT);

                if ($quantity <= 0) {
                    $errors['items'][$index]['quantity'] = 'Quantity must be greater than 0';
                }

                if ($price <= 0) {
                    $errors['items'][$index]['price'] = 'Price must be greater than 0';
                }

                $poData['items'][] = [
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'tax_rate_id' => $taxRateId,
                    'description' => $_POST['description'][$index] ?? ''
                ];
            }
        }
    }

    if (empty($poData['items'])) {
        $errors['items'] = 'At least one item is required';
    }

    // If no errors, save the PO
    if (empty($errors)) {
        try {
            $poId = $poSystem->createPurchaseOrder(
                $poData['supplier_id'],
                $_SESSION['user_id'],
                $poData['order_date'],
                $poData['expected_delivery_date'],
                $poData['notes'],
                $poData['terms'],
                $poData['items']
            );

            $_SESSION['flash_message'] = 'Purchase order created successfully!';
            redirect("view_po.php?id=$poId");
        } catch (Exception $e) {
            $errors[] = 'Error creating purchase order: ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create New Purchase Order</h2>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <?php if (is_array($error)): ?>
                        <?php foreach ($error as $subError): ?>
                            <li><?= is_array($subError) ? implode(', ', $subError) : $subError ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><?= $error ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Purchase Order Details</h4>
        </div>
        <div class="card-body">
            <form method="post" id="poForm">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['supplier_id']) ? 'is-invalid' : '' ?>"
                                    id="supplier_id" name="supplier_id" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>"
                                        <?= $poData['supplier_id'] == $supplier['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['supplier_id'])): ?>
                                <div class="invalid-feedback"><?= $errors['supplier_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="order_date" class="form-label">Order Date</label>
                            <input type="date" class="form-control" id="order_date" name="order_date"
                                   value="<?= htmlspecialchars($poData['order_date']) ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                            <input type="date" class="form-control" id="expected_delivery_date" name="expected_delivery_date"
                                   value="<?= htmlspecialchars($poData['expected_delivery_date']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?= htmlspecialchars($poData['notes']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="terms" class="form-label">Terms & Conditions</label>
                            <textarea class="form-control" id="terms" name="terms" rows="2"><?= htmlspecialchars($poData['terms']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Items</h5>
                        <div class="table-responsive">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Item</th>
                                        <th style="width: 25%">Description</th>
                                        <th style="width: 10%">Quantity</th>
                                        <th style="width: 10%">Price</th>
                                        <th style="width: 15%">Tax</th>
                                        <th style="width: 10%">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($poData['items'])): ?>
                                        <?php foreach ($poData['items'] as $index => $item): ?>
                                            <tr>
                                                <td>
                                                    <select class="form-select item-select" name="item_id[]" required>
                                                        <option value="">Select Item</option>
                                                        <?php foreach ($items as $itemOption): ?>
                                                            <option value="<?= $itemOption['id'] ?>"
                                                                data-price="<?= $itemOption['cost_price'] ?>"
                                                                <?= $item['item_id'] == $itemOption['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($itemOption['item_code'] . ' - ' . $itemOption['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="description[]"
                                                           value="<?= htmlspecialchars($item['name']) ?>">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control quantity" name="quantity[]"
                                                           min="0.01" step="0.01" value="<?= htmlspecialchars($item['quantity']) ?>" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control price" name="price[]"
                                                           min="0.01" step="0.01" value="<?= htmlspecialchars($item['price']) ?>" required>
                                                </td>
                                                <td>
                                                    <select class="form-select tax-rate" name="tax_rate_id[]">
                                                        <option value="">No Tax</option>
                                                        <?php foreach ($taxRates as $rate): ?>
                                                            <option value="<?= $rate['id'] ?>"
                                                                <?= $item['tax_rate_id'] == $rate['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($rate['name'] . ' (' . $rate['rate'] . '%)') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td class="item-total">0.00</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td>
                                                <select class="form-select item-select" name="item_id[]" required>
                                                    <option value="">Select Item</option>
                                                    <?php foreach ($items as $item): ?>
                                                        <option value="<?= $item['id'] ?>" data-price="<?= $item['cost_price'] ?>">
                                                            <?= htmlspecialchars($item['item_code'] . ' - ' . $item['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="description[]">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control quantity" name="quantity[]" min="0.01" step="0.01" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control price" name="price[]" min="0.01" step="0.01" required>
                                            </td>
                                            <td>
                                                <select class="form-select tax-rate" name="tax_rate_id[]">
                                                    <option value="">No Tax</option>
                                                    <?php foreach ($taxRates as $rate): ?>
                                                        <option value="<?= $rate['id'] ?>">
                                                            <?= htmlspecialchars($rate['name'] . ' (' . $rate['rate'] . '%)') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="item-total">0.00</td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" class="btn btn-secondary btn-sm" id="addItem">
                                                <i class="fas fa-plus"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Subtotal:</td>
                                        <td id="subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Tax:</td>
                                        <td id="tax-total">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="5" class="text-end">Total:</td>
                                        <td id="grand-total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="list_po.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new item row
    document.getElementById('addItem').addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const newRow = document.querySelector('#itemsTable tbody tr:last-child').cloneNode(true);

        // Clear values
        newRow.querySelector('.item-select').selectedIndex = 0;
        newRow.querySelector('input[name="description[]"]').value = '';
        newRow.querySelector('.quantity').value = '';
        newRow.querySelector('.price').value = '';
        newRow.querySelector('.tax-rate').selectedIndex = 0;
        newRow.querySelector('.item-total').textContent = '0.00';

        tbody.appendChild(newRow);
        addRowEventListeners(newRow);
    });

    // Add event listeners to existing rows
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        addRowEventListeners(row);
        calculateRowTotal(row);
    });

    // Calculate totals initially
    calculateTotals();

    // Form submission
    document.getElementById('poForm').addEventListener('submit', function(e) {
        // Validate at least one item has been added
        const itemSelects = document.querySelectorAll('.item-select');
        let hasItems = false;

        itemSelects.forEach(select => {
            if (select.value) hasItems = true;
        });

        if (!hasItems) {
            e.preventDefault();
            alert('Please add at least one item to the purchase order');
        }
    });
});

function addRowEventListeners(row) {
    // Remove row
    row.querySelector('.remove-item').addEventListener('click', function() {
        if (document.querySelectorAll('#itemsTable tbody tr').length > 1) {
            row.remove();
            calculateTotals();
        } else {
            alert('A purchase order must have at least one item');
        }
    });

    // Item select change - auto-fill price
    row.querySelector('.item-select').addEventListener('change', function() {
        const priceInput = row.querySelector('.price');
        const selectedOption = this.options[this.selectedIndex];

        if (selectedOption && selectedOption.dataset.price) {
            priceInput.value = selectedOption.dataset.price;
            calculateRowTotal(row);
            calculateTotals();
        }
    });

    // Quantity/price/tax rate changes
    row.querySelector('.quantity').addEventListener('input', () => {
        calculateRowTotal(row);
        calculateTotals();
    });

    row.querySelector('.price').addEventListener('input', () => {
        calculateRowTotal(row);
        calculateTotals();
    });

    row.querySelector('.tax-rate').addEventListener('change', () => {
        calculateRowTotal(row);
        calculateTotals();
    });
}

function calculateRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.price').value) || 0;
    const subtotal = quantity * price;

    row.querySelector('.item-total').textContent = subtotal.toFixed(2);
}

function calculateTotals() {
    let subtotal = 0;
    let taxTotal = 0;

    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const taxRateSelect = row.querySelector('.tax-rate');
        const taxRate = taxRateSelect.options[taxRateSelect.selectedIndex]?.dataset.rate || 0;

        const rowSubtotal = quantity * price;
        subtotal += rowSubtotal;

        if (taxRate) {
            taxTotal += rowSubtotal * (parseFloat(taxRate) / 100);
        }
    });

    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax-total').textContent = taxTotal.toFixed(2);
    document.getElementById('grand-total').textContent = (subtotal + taxTotal).toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>