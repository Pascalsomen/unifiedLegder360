<?php
require_once '../../classes/SchoolFeesSystem.php';
require_once '../../config.php';

$school = new SchoolFeesSystem($pdo);

$student_id = $_POST['student_id'];
$sponsor_id = $_POST['sponsor_id'];

$school->assignSponsorToStudent($sponsor_id, $student_id);
header("Location: ../school_fees/students_list.php?msg=Sponsor assigned");
exit;
