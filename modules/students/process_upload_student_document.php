<?php
require_once '../../classes/SchoolFeesSystem.php';
require_once '../../config.php';

$school = new SchoolFeesSystem($pdo);
$studentId = $_POST['student_id'];
$docType = $_POST['document_type'];
$notes = $_POST['notes'];

$uploadDir = '../../uploads/student_docs/';
$filename = basename($_FILES["document"]["name"]);
$targetFilePath = $uploadDir . time() . "_" . $filename;

if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFilePath)) {
    $school->uploadStudentDocument($studentId, $docType, $targetFilePath, $notes);
    header("Location: ../school_fees/student_documents.php?student_id=$studentId&msg=Uploaded");
    exit;
} else {
    die("Upload failed.");
}
