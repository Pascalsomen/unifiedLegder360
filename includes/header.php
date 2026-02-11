<?php session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__. '/../classes/HRSystem.php';
$hr = new HRSystem($pdo);
require_once __DIR__ . '/functions.php';
$loginlink =$base."login.php";
$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    //header(": login.php");
    echo "<script>window.location='$loginlink'</script>";
    exit();
}

if (!isset($_SESSION['email'])) {
    //header(": login.php");
    echo "<script>window.location='$loginlink'</script>";
    exit();
}


// Get current user data if logged in
$currentUser = [];
if (isset($_SESSION['email'])) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$_SESSION['email']]);
    $currentUser = $stmt->fetch();
}


function generateBreadcrumbs($baseUrl = '/')
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = array_filter(explode('/', trim($uri, '/')));
    $breadcrumbs = [];
    $path = $baseUrl;

    foreach ($segments as $index => $segment) {
        // Remove file extension if it's the last segment
        if ($index === array_key_last($segments)) {
            $segment = preg_replace('/\.php$/', '', $segment);
        }

        // Clean title for display
        $title = ucwords(str_replace(['-', '_'], ' ', $segment));

        // If it's the last, make it active (no link)
        if ($index === array_key_last($segments)) {
            $breadcrumbs[$title] = null;
        } else {
            $path .= $segment . '/';
            $breadcrumbs[$title] = $path;
        }
    }

    return $breadcrumbs;
}

$breadcrumbs = generateBreadcrumbs('/');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' : '' ?>Unified Legder 360</title>



<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>



    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $base?>assets/custom_css.css">
<style>
html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
    background-color:#F4F5FE;
}

.main-content {
    flex: 1;

}

.footer {
    margin-top: auto; /* This ensures the footer is at the bottom */
}
.container{
    background-color:white;
    padding:20px;
    border-radius:10px;
}
/* Custom Navbar Styling */
.navbar {
    background: linear-gradient(90deg, #111111, #1a1a1a);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
}

.navbar-brand {
    font-size: 1.5rem;
    font-weight: bold;
    color: #ffc107 !important;
}

.navbar-nav .nav-link {
    color: #f8f9fa !important;
    padding: 10px 15px;
    transition: background 0.3s, color 0.3s;
    border-radius: 8px;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link:focus {
    background-color: #343a40;
    color: #ffc107 !important;
}

.dropdown-menu {
    background-color: #212529;
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 0;
}

.dropdown-item {
    color: #dee2e6;
    padding: 10px 20px;
    transition: background 0.3s, color 0.3s;
}

.dropdown-item:hover {
    background-color: #343a40;
    color: #ffc107;
}

.navbar-toggler {
    border-color: #ffc107;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,193,7, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

/* Right-side user dropdown */
.navbar-nav.ms-auto .nav-link {
    color: #ffc107 !important;
}




</style>
<style>
@media print {
    form, button {
        display: none;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
    }
}
</style>
<style>
    #loaderOverlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(65, 58, 42, 0.5); /* Dark yellow with transparency */
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      opacity: 1;
      transition: opacity 0.5s ease;
    }

    #loaderOverlay.fade-out {
      opacity: 0;
      pointer-events: none;
    }

    .table-custom {
    background-color: #ffffff;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

/* Table Header */
.table-custom thead {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

/* Table Cells */
.table-custom td, .table-custom th {
    padding: 12px 16px;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

/* Hover Effect */
.table-custom tbody tr:hover {
    background-color: #f1f9ff;
}

/* Zebra striping */
.table-custom tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}
  </style>
</head>
<body>
    <!-- Toast container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
  <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        <?php echo $_SESSION['toast'] ?? ''; ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<div id="loaderOverlay">
  <div class="text-center">
    <div class="spinner-border text-dark" role="status" style="width: 2rem; height: 2rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
</div>

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color:black">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?PHP echo $base;?>">
                 <?php echo $settings['system_name_short'] ?> <?php


                ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto">
                    <?php if(hasRole('accountant')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="accountingDropdown" role="button" data-bs-toggle="dropdown">
                      Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/chart_of_accounts.php">Chart of Accounts</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/journal_entries.php">Journal Entries</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/trial_balance.php">Trial Balance</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/general_ledger.php">General Ledger</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/account_ledger.php">Account Ledger</a></li>

                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/balanceSheet.php">Balance Sheet</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/income_statment.php">Income Statement</a></li>
                                     <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/notes.php">Notes</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/accounting/accounting_periods.php">Accounting Periods</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasRole('inventory') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button" data-bs-toggle="dropdown">
                              Inventory
                            </a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/stock_items.php">Stock Items</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/stock_report.php">Stock Movements</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/requisitions.php">Requisitions</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/list_suppliers.php">Suppliers</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/list_inventory_categories.php">Inventory category</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/assets.php">Company Assets</a></li>


                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (hasRole('hr') ): ?>




  <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button" data-bs-toggle="dropdown">
                              Contract Managment
                            </a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" href="<?= $base_url ?>/contracts/Contract_list.php">Contract Lists</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/inventory/purchase_orders.php">Purchase Orders</a></li>


                            </ul>
                        </li>



                    <?php endif; ?>

                    <?php if (hasRole('hr') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                 HR
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base_url ?>/hr/employees_list.php">Employees</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/hr/payroll_dashboard.php">Payroll Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/hr/payroll_list.php">Payroll List</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/hr/create_payroll.php">Create Payroll</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>


                    <?php if (hasRole('budgeting') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                Budgeting
                            </a>
                            <ul class="dropdown-menu">

                                <li><a class="dropdown-item" href="<?= $base_url ?>/budgeting/projects.php">Projects Bugdeting</a></li>

                                <li><a class="dropdown-item" href="<?= $base_url ?>/budgeting/all_projects_report.php">Reports</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>



                    <?php if (hasRole('school') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                 Students
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/students_list.php">Students</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/sponsor_list.php">Sponsors</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/terms_list.php">Terms</a></li>

                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/fee_payment.php">Record Fee payment</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/view_bulk_payslip.php">Payslips</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/students/school_fee_report.php">Reports</a></li>

                            </ul>
                        </li>
                    <?php endif; ?>






                    <?php if (hasRole('loan') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                 Loan
                            </a>
                            <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/loan_dashboard.php">Loan Summary</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/paid_loans.php">Paid Loans</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/late_repayments.php">Late Repayments</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/upcoming_repayments.php">Upcoming Repayments</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/borrower_list.php">Borrowers</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/loan_create.php">Record Loan</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/loans/loan_list.php">Loans</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>





                    <?php if (hasRole('donations') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                 Donations
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base_url ?>/donations/donors.php">Donors</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/donations/record_donation.php">Record Donations</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/donations/list.php">Donations</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/donations/report.php">Report</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>


                    <?php if (hasRole('admin') ): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button" data-bs-toggle="dropdown">
                                 Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base ?>/system.php">System Settings</a></li>
                                <li><a class="dropdown-item" href="<?= $base ?>/backup_email.php">Manual Backup</a></li>
                                <li><a class="dropdown-item" href="<?= $base ?>/restore.php">Restore Backup</a></li>

                            </ul>
                        </li>
                    <?php endif; ?>


                </ul>

                <!-- User Dropdown -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> My Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">

                            <li><a class="dropdown-item" href="<?= $base ?>/change_password.php"><i class="fas fa-cog me-2"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $base ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container-fluid mt-3 main-content">
        <!-- System Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo $base ?>"><i class="fas fa-home"></i></a></li>
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $title => $link): ?>
                        <?php if ($link): ?>
                            <li class="breadcrumb-item"><a href="<?= $link ?>"><?= $title ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </nav>


