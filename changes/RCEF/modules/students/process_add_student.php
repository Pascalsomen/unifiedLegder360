<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

$uploadDir = __DIR__ . '/../uploads/student_documents/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$data = [
    'first_name' => $_POST['first_name'],
    'last_name' => $_POST['last_name'],
    'dob' => $_POST['dob'],
    'gender' => $_POST['gender'],
    'address' => $_POST['address'],
    'phone' => $_POST['phone'],
    'school_id' => $_POST['school_id'],
    'sponsor_id' => $_POST['sponsor_id'],
    'fees_payment' => $_POST['fees_payment'],
    'bank_name' => $_POST['bank_name'],
    'bank_account' => $_POST['bank_account'],
    'father_name' => $_POST['father_name'],
    'mother_name' => $_POST['mother_name'],
    'guardian_name' => $_POST['guardian_name'],
    'grade' => $_POST['grade'],



    'documents' => []
];

$uploadDir = __DIR__ . '/../uploads/student_documents/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $key => $name) {
        $tmpName = $_FILES['documents']['tmp_name'][$key];
        $error = $_FILES['documents']['error'][$key];
        $fileType = $_FILES['documents']['type'][$key];

        if ($error === UPLOAD_ERR_OK) {
            $safeName = time() . '_' . basename($name);
            $destination = $uploadDir . $safeName;

            if (move_uploaded_file($tmpName, $destination)) {
                // Save info to pass to DB
                $data['documents'][] = [
                    'filename' => $safeName,
                    'filetype' => $fileType
                ];
            }
        }
    }
}

$studentId = $school->addStudent($data);
echo "<script>window.location ='students_list.php'</script>";
exit;
