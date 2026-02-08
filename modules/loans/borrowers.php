<?php
require_once __DIR__ . '/../../includes/header.php';  // Include header
require_once '../../classes/SchoolFeesSystem.php';   // Include the class where DB connection is established

$school = new SchoolFeesSystem($pdo);  // Create an instance of the SchoolFeesSystem class

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //$id_number = $_POST['id_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $account_number = $_POST['account_number'];



    $idnumber = $_POST['idnumber'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $sector = $_POST['sector'];
    $cell = $_POST['cell'];
    $village = $_POST['village'];






    $accountCode =42;
    $projectCode ='02R';


    $stmt = $pdo->prepare("
    SELECT id_number FROM borrower
    WHERE id_number LIKE ?
    ORDER BY id_number DESC LIMIT 1
");


$stmt->execute(["$accountCode%$projectCode"]);
$lastId = $stmt->fetchColumn();

if ($lastId && preg_match('/^(\d{2})(\d{3})([A-Z0-9]+)$/', $lastId, $matches)) {
    $currentNumber = (int)$matches[2];
    $nextNumber = str_pad($currentNumber + 1, 3, '0', STR_PAD_LEFT);
} else {
    $nextNumber = '001';
}





$id_number =$accountCode.$nextNumber.$projectCode;



    // Insert borrower into the database
    $stmt = $pdo->prepare("INSERT INTO borrower (id_number, first_name, last_name, address, phone_number, email,idnumber,province,district,sector,cell,village,account_number)
                           VALUES (:id_number, :first_name, :last_name, :address, :phone_number, :email , :idnumber , :province , :district , :sector , :cell, :village, :account_number)");
    $stmt->execute([
        'id_number' => $id_number,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'address' => $address,
        'phone_number' => $phone_number,
        'email' => $email,
        'idnumber' => $idnumber,
        'province' => $province,
        'district' => $district,
        'sector' => $sector,
        'cell' => $cell,
        'village' => $village,
        'account_number' => $account_number,



    ]);


    $accountCode = $id_number;
    $accountName = $first_name. " ".$last_name;
    $accountType = 'receivables';
    $parentAccount = 1;

    $stmt = $pdo->prepare("INSERT INTO chart_of_accounts
                          (account_code, account_name, account_type, parent_id)
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$accountCode, $accountName, $accountType, $parentAccount]);

    echo "<script>alert('Borrower added successfully');</script>";
}
?>
<div class="container mt-4">
<h3>Add  New Borrower</h3>
<form method="POST" action="">
    <label for="id_number">Account Number:</label>
    <input type="text" value="Auto-generated" disabled class="form-control" name="id_number" id="id_number" required>

    <label for="first_name">First Name:</label>
    <input type="text" class="form-control" name="first_name" id="first_name" required>

    <label for="last_name">Last Name:</label>
    <input type="text" class="form-control" name="last_name" id="last_name" required>

    <label for="last_name">ID Number</label>
    <input type="text" class="form-control" name="idnumber" id="idnumber" required>
    <label for="account_number">Bank Account Number</label>
    <input type="text" class="form-control" name="account_number" id="account_number" required>



    <label for="address">Province:</label>
    <textarea name="province" class="form-control" id="province" required></textarea>


    <label for="address">District:</label>
    <textarea name="district" class="form-control" id="district" required></textarea>


    <label for="address">Sector:</label>
    <textarea name="sector" class="form-control" id="sector" required></textarea>

        <label for="address">Cell:</label>
    <textarea name="cell" class="form-control" id="cell" required></textarea>

        <label for="address">Village:</label>
    <textarea name="village" class="form-control" id="village" required></textarea>

    <label for="address">Additional Address:</label>
    <textarea name="address" class="form-control" id="address" ></textarea>

    <label for="phone_number">Phone Number:</label>
    <input type="text"  class="form-control" name="phone_number" id="phone_number" required>

    <label for="email">Email:</label>
    <input type="email" class="form-control" name="email" id="email" >

    <br>
    <?php if(hasPermission(37)){?>

        <button class="btn btn-primary" type="submit">Add Borrower</button>
      <?php }else{
         Echo "You do not have access to add new borrower";
      } ?>

</form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
