<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
    SELECT account_number FROM suppliers
    WHERE account_number LIKE ?
    ORDER BY id DESC LIMIT 1
");

$accountCode =41;
$stmt->execute(["$accountCode%"]);
$lastId = $stmt->fetchColumn();

if (preg_match('/^(\d{2})(\d{3})$/', $lastId, $matches)) {
    $prefix = $matches[1]; // e.g. "41"
    $number = (int)$matches[2]; // e.g. 001

    $nextNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT); // e.g. "002"

    $prefix . $nextNumber; // e.g. "41002"
    $id_number =$prefix . $nextNumber;
}else{
    $id_number = 41001;
}


    $names = $_POST['name'];
    $data = [
        'name' => $_POST['name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'tax_id' => $_POST['tax_id'],
        'supplier_contact_person' => $_POST['supplier_contact_person'],
        'is_active' => 1,
        'account_number' =>   $id_number// generated


    ];









    $stmt = $pdo->prepare("
        INSERT INTO suppliers (name, contact_person, email, phone, address, tax_id, is_active, supplier_contact_person,account_number)
        VALUES (:name, :contact_person, :email, :phone, :address, :tax_id, :is_active, :supplier_contact_person,:account_number)
    ");

    $stmt->execute($data);

    $accountCode = $id_number;
    $accountName =   $names;
    $accountType = 'liability';
    $parentAccount = 1;

    $stmt = $pdo->prepare("INSERT INTO chart_of_accounts
                          (account_code, account_name, account_type, parent_account)
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$accountCode, $accountName, $accountType, $parentAccount]);

    echo "<div class='alert alert-success'>Supplier added successfully!</div>";
}
?>

<div class="container mt-4">
    <h3>Add New Supplier</h3>
    <form method="POST">
    <div class="mb-3">
            <label for="name" class="form-label">Account Number</label>
            <input type="text" class="form-control" disabled value="Auto-generated" required>
        </div>

        <div class="mb-3">
            <label for="tax_id" class="form-label">Tax Identification Number (TIN)</label>
            <input type="text" class="form-control" name="tax_id">
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Supplier Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="mb-3">
            <label for="contact_person" class="form-label">Primary Contact Person</label>
            <input type="text" class="form-control" name="contact_person" required>
        </div>

        <div class="mb-3">
            <label for="supplier_contact_person" class="form-label">Alternative Contact Person</label>
            <input type="text" class="form-control" name="supplier_contact_person">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="2"></textarea>
        </div>

        <?php if(hasPermission(17)){ ?>
            <button type="submit" class="btn btn-primary">Add Supplier</button>

 <?php }else{
    echo "You do not have access to add supplier";
 }?>


    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
