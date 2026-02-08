<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

$docId = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;

if ($docId){
    $school->deleteStudent($docId);
    echo "<script>window.location=view_student.php?id=" . urlencode($studentId) ."</script>";
}


exit;
