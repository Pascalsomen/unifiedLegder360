<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Inventory.php';
require_once __DIR__ . '/../../classes/InventoryCategory.php';
require_once __DIR__ . '/../../classes/Supplier.php';

if (!hasPermission('manage_inventory')) {
    redirect('/index.php');
}

$inventorySystem = new Inventory($pdo);
$categorySystem = new InventoryCategory($pdo);
$supplierSystem = new Supplier($pdo);

// Get data for dropdowns
$categories = $categorySystem->getCategories();
$suppliers = $supplierSystem->getActiveSuppliers();
$unitsOfMeasure = ['pcs', 'kg', 'g', 'lb', 'oz', 'l', 'ml', 'box', 'pack', 'set', 'pair'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $itemData = [
            'item_code' => $_POST['item_code'],
            'item_name' => $_POST['item_name'],
            'description' => $_POST['description'],
            'category_id' => $_POST['category_id'] ?: null,
            'supplier_id' => $_POST['supplier_id'] ?: null,
            'unit_of_measure' => $_POST['unit_of_measure'],
            'cost_price' => $_POST['cost_price'],
            'selling_price' => $_POST['selling_price'],
            'reorder_level' => $_POST['reorder_level'],
            'quantity_on_hand' => $_POST['quantity_on_hand'],
            'is_active' => isset($_POST['is_active']),
            'image_path' => $_POST['image_path'] ?? null
        ];

        if (isset($_GET['id'])) {
            // Update existing item
            $success = $inventorySystem->updateItem($_GET['id'], $itemData);
            if ($success) {
                $_SESSION['success'] = "Item updated successfully!";
            } else {
                $_SESSION['error'] = "No changes were made to the item.";
            }
        } else {
            // Create new item
            $itemId = $inventorySystem->createItem($itemData, $_SESSION['user_id']);
            $_SESSION['success'] = "Item created successfully!";
            redirect("/modules/inventory/view_item.php?id=$itemId");
        }

        redirect("/modules/inventory/view_item.php?id=" . ($_GET['id'] ?? $itemId));
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get item data if editing
$item = null;
if (isset($_GET['id'])) {
    $item = $inventorySystem->getItem($_GET['id']);
    if (!$item) {
        $_SESSION['error'] = "Item not found";
        redirect('/modules/inventory/inventory_dashboard.php');
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2><?= isset($item) ? 'Edit' : 'Add' ?> Inventory Item</h2>
            <?php if (isset($item)): ?>
                <p class="text-muted">Item Code: <?= htmlspecialchars($item['item_code']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="mb-3">
                            <label class="form-label">Item Code*</label>
                            <input type="text" class="form-control" name="item_code"
                                   value="<?= htmlspecialchars($item['item_code'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item Name*</label>
                            <input type="text" class="form-control" name="item_name"
                                   value="<?= htmlspecialchars($item['item_name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id">
                                    <option value="">No Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"
                                            <?= isset($item['category_id']) && $item['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <select class="form-select" name="supplier_id">
                                    <option value="">No Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>"
                                            <?= isset($item['supplier_id']) && $item['supplier_id'] == $supplier['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($supplier['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Pricing & Stock -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cost Price*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="cost_price" step="0.01" min="0"
                                           value="<?= htmlspecialchars($item['cost_price'] ?? '0.00') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Selling Price*</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="selling_price" step="0.01" min="0"
                                           value="<?= htmlspecialchars($item['selling_price'] ?? '0.00') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Unit of Measure*</label>
                                <select class="form-select" name="unit_of_measure" required>
                                    <option value="">Select Unit</option>
                                    <?php foreach ($unitsOfMeasure as $unit): ?>
                                        <option value="<?= $unit ?>"
                                            <?= isset($item['unit_of_measure']) && $item['unit_of_measure'] == $unit ? 'selected' : '' ?>>
                                            <?= strtoupper($unit) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" name="reorder_level" min="0"
                                       value="<?= htmlspecialchars($item['reorder_level'] ?? '0') ?>">
                            </div>
                        </div>

                        <?php if (!isset($item)): ?>
                            <div class="mb-3">
                                <label class="form-label">Initial Quantity</label>
                                <input type="number" class="form-control" name="quantity_on_hand" min="0"
                                       value="<?= htmlspecialchars($item['quantity_on_hand'] ?? '0') ?>">
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                       <?= !isset($item) || $item['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">
                                    Active Item
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <input type="file" class="form-control" name="item_image" accept="image/*">
                            <?php if (isset($item['image_path']) && $item['image_path']): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" style="max-height: 100px;">
                                    <input type="hidden" name="image_path" value="<?= htmlspecialchars($item['image_path']) ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="inventory_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= isset($item) ? 'Update' : 'Save' ?> Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>