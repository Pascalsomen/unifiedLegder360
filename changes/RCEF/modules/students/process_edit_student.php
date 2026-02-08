<?php require_once '../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id     = $_POST['student_id'];
    $first_name     = $_POST['first_name'];
    $last_name      = $_POST['last_name'];
    $phone          = $_POST['phone'];
    $dob            = $_POST['dob'];
    $gender         = $_POST['gender'];
    $school_name    = $_POST['school_id'];
    $fees_payment   = $_POST['fees_payment'];
    $bank_name      = $_POST['bank_name'];
    $bank_account   = $_POST['bank_account'];
    $father_name    = $_POST['father_name'];
    $mother_name    = $_POST['mother_name'];
    $guardian_name  = $_POST['guardian_name'] ?? '';
    $grade          = $_POST['grade'] ?? '';
    $sponsor_id     = $_POST['sponsor_id'] ?? null;
    $address        = $_POST['address'] ?? '';
    $is_active      = isset($_POST['is_active']) ? 1 : 0;

    try {
        // Update students table
        $stmt = $pdo->prepare("
            UPDATE students SET
                first_name = ?,
                last_name = ?,
                phone = ?,
                dob = ?,
                gender = ?,
                school_name = ?,
                fees_payment = ?,
                bank_name = ?,
                bank_account = ?,
                father_name = ?,
                mother_name = ?,
                guardian_name = ?,
                grade = ?,
                address = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $first_name,
            $last_name,
            $phone,
            $dob,
            $gender,
            $school_name,
            $fees_payment,
            $bank_name,
            $bank_account,
            $father_name,
            $mother_name,
            $guardian_name,
            $grade,
            $address,
            $is_active,
            $student_id
        ]);

        // Handle sponsor relationship
        if (!empty($sponsor_id)) {
            // Check if a sponsor record already exists for this student
            $checkStmt = $pdo->prepare("SELECT id FROM student_sponsor WHERE student_id = ?");
            $checkStmt->execute([$student_id]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                echo 0;
                // Update sponsor
                $updateSponsor = $pdo->prepare("UPDATE student_sponsor SET sponsor_id = ? WHERE student_id = ?");
                $updateSponsor->execute([$sponsor_id, $student_id]);
            } else {
                // Insert new sponsor relationship
                $insertSponsor = $pdo->prepare("INSERT INTO student_sponsor (student_id, sponsor_id, created_at) VALUES (?, ?, NOW())");
                $insertSponsor->execute([$student_id, $sponsor_id]);
                echo 1;
            }
        }else{
             echo 0;
        }

        $_SESSION['toast']= 'Edited Successfully';
        echo "<script>window.location='view_student.php?id=$student_id ' </script>";
        exit;

    } catch (PDOException $e) {
        die("Error updating student: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}
