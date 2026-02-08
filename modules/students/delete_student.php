<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

$studentId = $_GET['id'] ?? null;
if (!$studentId) exit("Missing ID");

// Delete documents
$docs = $school->getStudentDocuments($studentId);
foreach ($docs as $doc) {
    $filePath = __DIR__ . '/uploads/student_documents/' . $doc['document_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}



$school->deleteStudent($studentId);

echo "<script>window.location='students_list.php'</script>";
exit;
