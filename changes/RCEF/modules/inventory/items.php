<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('inventory')) {
    redirect('/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemCode = $_POST['item_code'];
    $barcode = $_POST['barcode'] ?? null;
    $name = $_POST['name'];
    $categoryId = $_POST['category_id'];
    $supplierId = $_POST['supplier_id'];
    $description = $_POST['description'];
    $unitOfMeasure = $_POST['unit_of_measure'];
    $costPrice = $_POST['cost_price'];
    $sellingPrice = $_POST['selling_price'];
    $reorderLevel = $_POST['reorder_level'];

    try {
        $stmt = $pdo->prepare("INSERT INTO inventory_items
                              (item_code, barcode, name, category_id, supplier_id, description,
                               unit_of_measure, cost_price, selling_price, reorder_level)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$itemCode, $barcode, $name, $categoryId, $supplierId, $description,
                       $unitOfMeasure, $costPrice, $sellingPrice, $reorderLevel]);

        $_SESSION['success'] = "Item added successfully!";
        redirect('/modules/inventory/items.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding item: " . $e->getMessage();
    }
}

// Fetch existing data
$categories = $pdo->query("SELECT * FROM inventory_categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
$items = $pdo->query("SELECT i.*, c.name as category_name, s.name as supplier_name
                      FROM inventory_items i
                      LEFT JOIN inventory_categories c ON i.category_id = c.id
                      LEFT JOIN suppliers s ON i.supplier_id = s.id
                      ORDER BY i.name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Inventory Item</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Item Code*</label>
                                    <input type="text" class="form-control" name="item_code" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Barcode</label>
                                    <input type="text" class="form-control" name="barcode">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Item Name*</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="category_id">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select class="form-control" name="supplier_id">
                                        <option value="">-- Select Supplier --</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit of Measure</label>
                                    <input type="text" class="form-control" name="unit_of_measure" placeholder="e.g., pcs, kg, L">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cost Price*</label>
                                    <input type="number" class="form-control" name="cost_price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Selling Price*</label>
                                    <input type="number" class="form-control" name="selling_price" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Reorder Level</label>
                            <input type="number" class="form-control" name="reorder_level" step="0.01" min="0">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Inventory Items</h4> <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Stock')">Export to Excel</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table  id="table" class="table table-striped datatable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>In Stock</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr class="<?= $item['current_quantity'] <= $item['reorder_level'] ? 'table-warning' : '' ?>">
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['category_name'] ?? 'N/A' ?></td>
                                    <td>
                                        <?= $item['current_quantity'] ?>
                                        <?= $item['unit_of_measure'] ?>
                                        <?php if ($item['current_quantity'] <= $item['reorder_level']): ?>
                                            <span class="badge badge-danger">Low Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($item['selling_price'], 2) ?></td>
                                    <td>
                                        <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="adjust_stock.php?item_id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-cubes"></i>
                                        </a>
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

<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting for actions column
        ]
    });

    // Barcode generation
    $('#generateBarcode').click(function() {
        const itemCode = $('input[name="item_code"]').val();
        if (itemCode) {
            $('input[name="barcode"]').val('ITM-' + itemCode + '-' + Math.floor(1000 + Math.random() * 9000));
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>