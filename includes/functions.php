<?php
require_once __DIR__ . '/../config/database.php';
$base_url = 'http://localhost/UnifiedLegder/modules/';
$base = 'http://localhost/UnifiedLegder/';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($roleName) {
    global $pdo;

    if (!isLoggedIn()) return false;

    require_once __DIR__ . '/../classes/HRSystem.php';
    $hr = new HRSystem($pdo);

    $employeeId = $hr->getEmployeeIdByUserId($_SESSION['email']);
    if (!$employeeId) return false;

    // Get role ID
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = :roleName");
    $stmt->execute(['roleName' => $roleName]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) return false;

    // Check if employee has that role
    $stmt = $pdo->prepare("SELECT 1 FROM employee_roles WHERE employee_id = :empId AND role_id = :roleId");
    $stmt->execute([
        'empId' => $employeeId,
        'roleId' => $role['id']
    ]);

    return $stmt->fetchColumn() !== false;
}

function hasPermission($permissionId)
{
    global $pdo;

    if (!isset($_SESSION['email'])) return false;

    // Get employee ID
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
    $stmt->execute([$_SESSION['email']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$employee) return false;

    $employeeId = $employee['id'];

    // Check if permission is directly assigned to employee
    $stmt = $pdo->prepare("
        SELECT 1 FROM employee_permissions
        WHERE employee_id = ? AND permission_id = ?
    ");
    $stmt->execute([$employeeId, $permissionId]);
    //if ($stmt->fetch()) return true;
    return $stmt->fetch() ? true : false;

    // // Check if permission is granted through a role
    // $stmt = $pdo->prepare("
    //     SELECT 1 FROM role_permissions rp
    //     JOIN employee_roles er ON rp.role_id = er.role_id
    //     WHERE er.employee_id = ? AND rp.permission_id = ?
    // ");
    // $stmt->execute([$employeeId, $permissionId]);
    // return $stmt->fetch() ? true : false;
}



function hasSubPermission($roleName, $permissionName) {
    global $pdo;

    if (!isLoggedIn()) return false;

    require_once __DIR__ . '/../classes/HRSystem.php';
    $hr = new HRSystem($pdo);

    $employeeId = $hr->getEmployeeIdByUserId($_SESSION['email']);
    if (!$employeeId) return false;

    // Get role ID
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = :roleName");
    $stmt->execute(['roleName' => $roleName]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$role) return false;

    // Check employee_permissions
    $stmt = $pdo->prepare("
        SELECT 1 FROM employee_permissions
        WHERE employee_id = :employeeId
          AND permission_id = :roleId
          AND permission_name = :permissionName
    ");
    $stmt->execute([
        'employeeId' => $employeeId,
        'roleId' => $role['id'],
        'permissionName' => $permissionName
    ]);

    return $stmt->fetchColumn() !== false;
}





function formatCurrency($amount, $currency = 'USD') {
    $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, $currency);
}

function redirect($url) {
    //header("Location: $url");
    echo "<script>window.location='$url'</script>";
     exit();
}

function getCurrentFiscalYear() {
    $month = date('n');
    $year = date('Y');
    return ($month < 7) ? ($year - 1) . '-' . $year : $year . '-' . ($year + 1);
}
?>