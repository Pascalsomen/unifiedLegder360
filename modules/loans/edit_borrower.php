<?php
require_once __DIR__ . '/../../includes/header.php';  // Include header
require_once '../../classes/SchoolFeesSystem.php';   // Include the class where DB connection is established

$school = new SchoolFeesSystem($pdo);  // Create an instance of the SchoolFeesSystem class

// Check if borrower ID is set in the URL
if (isset($_GET['id'])) {
    $borrower_id = $_GET['id'];

    // Fetch borrower details from the database
    $stmt = $pdo->prepare("SELECT * FROM borrower WHERE id = :id");
    $stmt->execute(['id' => $borrower_id]);
    $borrower = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the borrower doesn't exist, show an error message
    if (!$borrower) {
        echo "Borrower not found!";
        exit;
    }
}

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //$id_number = $_POST['id_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    $idnumber = $_POST['idnumber'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $sector = $_POST['sector'];
    $cell = $_POST['cell'];
    $village = $_POST['village'];
    $account_number = $_POST['account_number'];

    // Update the borrower's details in the database
    $stmt = $pdo->prepare("UPDATE borrower SET  first_name = :first_name, last_name = :last_name, address = :address, phone_number = :phone_number, email = :email  
    , idnumber = :idnumber , province = :province , district = :district , sector = :sector , cell = :cell , village = :village , account_number  = :account_number WHERE id = :id");
    $stmt->execute([

        'first_name' => $first_name,
        'last_name' => $last_name,
        'address' => $address,
        'phone_number' => $phone_number,
        'email' => $email,
        'id' => $borrower_id,
        'idnumber' => $idnumber,
        'province' => $province,
        'district' => $district,
        'sector' => $sector,
        'cell' => $cell,
        'village' => $village,
        'account_number' => $account_number,
        
    ]);

    // Redirect back to the borrowers list after successful update
   // header('Location: list_borrowers.php');
    echo "<script>window.location= 'borrower_list.php'</script>";
    exit;
}
?>
<div class="container mt-4">
<h2>Edit Borrower</h2>

<form method="POST" action="">
    <label for="id_number">ID Number:</label>
    <input type="text" class="form-control" name="id_number" disabled id="id_number" value="<?php echo htmlspecialchars($borrower['id_number']); ?>" required>

    <label for="first_name">First Name:</label>
    <input type="text"  class="form-control" name="first_name" id="first_name" value="<?php echo htmlspecialchars($borrower['first_name']); ?>" required>

    <label for="last_name">Last Name:</label>
    <input type="text"  class="form-control" name="last_name" id="last_name" value="<?php echo htmlspecialchars($borrower['last_name']); ?>" required>






 <label for="last_name">ID Number</label>
    <input type="text" class="form-control" name="idnumber" id="idnumber" value="<?php echo htmlspecialchars($borrower['idnumber']); ?>" required>
    


     <label for="last_name">Bank Account Number</label>
    <input type="text" class="form-control" name="account_number" id="account_number" value="<?php echo htmlspecialchars($borrower['account_number']); ?>" required>
    


    <label for="address">Province:</label>
    <textarea name="province" class="form-control" id="province" required><?php echo htmlspecialchars($borrower['province']); ?></textarea>


    <label for="address">District:</label>
    <textarea name="district" class="form-control" id="district" required><?php echo htmlspecialchars($borrower['district']); ?></textarea>


    <label for="address">Sector:</label>
    <textarea name="sector" class="form-control" id="sector" required><?php echo htmlspecialchars($borrower['sector']); ?></textarea>

        <label for="address">Cell:</label>
    <textarea name="cell" class="form-control" id="cell" required><?php echo htmlspecialchars($borrower['cell']); ?></textarea>

        <label for="address">Village:</label>
    <textarea name="village" class="form-control" id="village" required><?php echo htmlspecialchars($borrower['village']); ?></textarea>





    <label for="address">Address:</label>
    <textarea name="address" class="form-control" id="address" required><?php echo htmlspecialchars($borrower['address']); ?></textarea>

    <label for="phone_number">Phone Number:</label>
    <input type="text" class="form-control" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($borrower['phone_number']); ?>" required>

    <label for="email">Email:</label>
    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($borrower['email']); ?>" required>

    <button type="submit" class="btn btn-info">Update Borrower</button>
</form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
