<?php
require_once 'LoanSystem.php';
require_once 'AccountingSystem.php';

class HRSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPayrollsByMonth($month) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, e.full_name
            FROM payrolls p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.month = ?
            ORDER BY p.month DESC
        ");
        $stmt->execute([$month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDistinctMonths() {
        $stmt = $this->pdo->query("
            SELECT DISTINCT DATE_FORMAT(month, '%Y-%m') AS month
            FROM payrolls
            ORDER BY month DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPayrollStatss($month = null) {
        $query = "
            SELECT
                SUM(gross_salary) AS total_gross,
                SUM(net_salary) AS total_net,
                SUM(total_deductions) AS total_deductions,
                SUM(total_employer_contribution) AS total_employer_contribution
            FROM payrolls
        ";

        if ($month) {
            $query .= " WHERE DATE_FORMAT(payment_date, '%Y-%m') = :month";
        }

        $stmt = $this->pdo->prepare($query);

        if ($month) {
            $stmt->execute(['month' => $month]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSalariesForLast6Months() {
        $stmt = $this->pdo->query("
            SELECT
                DATE_FORMAT(month, '%Y-%m') AS month,
                SUM(gross_salary) AS total_salary
            FROM payrolls
            WHERE month >= NOW() - INTERVAL 6 MONTH
            GROUP BY month
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getPayrollStats() {
        $stmt = $this->pdo->query("
            SELECT
                SUM(gross_salary) AS total_gross,
                SUM(net_salary) AS total_net,
                SUM(total_deductions) AS total_deductions,
                SUM(total_employer_contribution) AS total_employer_contribution
            FROM payrolls
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addEmployee(array $employeeData): int {
        $this->pdo->beginTransaction();

        try {
            // Insert employee
            $stmt = $this->pdo->prepare("
                INSERT INTO employees (full_name, email, phone, salary, created_at, is_active,hire_date)
                VALUES (:full_name, :email, :phone, :salary, NOW(), 1,NOW())
            ");
            $stmt->execute([
                ':full_name' => $employeeData['full_name'],
                ':email' => $employeeData['email'],
                ':phone' => $employeeData['phone'],
                ':salary' => $employeeData['salary']
            ]);

            $employeeId = $this->pdo->lastInsertId();

          // $employeeId = $hrSystem->createUserFromEmployee($employeeData); // or update


            // // Create reductions record
            // $stmt = $this->pdo->prepare("
            //     INSERT INTO reductions (employee_id, tax, pension, contribution)
            //     VALUES (:employee_id, :tax, :pension, :contribution)
            // ");
            // $stmt->execute([
            //     ':employee_id' => $employeeId,
            //     ':tax' => $employeeData['tax'],
            //     ':pension' => $employeeData['pension'],
            //     ':contribution' => $employeeData['contribution']
            // ]);

            // Auto-generate password and hash it
            $rawPassword = $employeeData['phone']; // 8-char random password
            $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

            // Create user account
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, password, email, role, last_login, created_at, is_active)
                VALUES (:username, :password, :email, :role, NULL, NOW(), 1)
            ");
            $stmt->execute([
                ':username' => $employeeData['email'],
                ':password' => $hashedPassword,
                ':email' => $employeeData['email'],
                ':role' => 'user'
            ]);

            $this->pdo->commit();

            // Optionally log or return the raw password
            // For now, return it for use in onboarding
            return $employeeId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function calculateAndSavePayroll($employeeId, $grossSalary, $transport, $month) {
        $D = $grossSalary;
        $E = $transport;



        if ($D > 200000) {
            // Salary above 200,000
            $PAYE  = (($D - 200000) * 0.30) + 24000;
        } elseif ($D >= 100001 && $D <= 200000) {
            // Salary between 100,001 and 200,000
            $PAYE = (( $D- 100000) * 0.20) + 4000;
        } elseif ( $D>= 60001 &&   $D <= 100000) {
            // Salary between 60,001 and 100,000
            $PAYE = ($D - 60000) * 0.10;
        } else {
            // Salary 60,000 or less — no tax
            $PAYE  = 0;
        }

        // Employee Contributions
        $F =  $D * 0.06;
        $MAT_EMP = $D * 0.003;
        $CBHI_EMP = 0;
        $totalDeduction = $F + $PAYE + $MAT_EMP + $CBHI_EMP;

        // Employer Contributions
        $G = $D * 0.08; // Pension
        $H = ($D - $E) * 0.02; // Occupational hazard
        $RAMA_EMPLOYER = 0 ;
        $MAT_EMPLOYER = $D * 0.003;
        $CBHI_EMPLOYER = 0;
        $totalEmployer = $G + $H  + $MAT_EMPLOYER + $CBHI_EMPLOYER;

        // Net Salary
        $net = $D - $totalDeduction;

        // Save to DB
        $stmt = $this->pdo->prepare("INSERT INTO payrolls (
            employee_id, gross_salary, transport, emp_pension, emp_rama, emp_maternity, emp_cbhi, total_deductions,
            employer_pension, employer_occupational, employer_rama, employer_maternity, employer_cbhi, total_employer_contribution,
            net_salary, month
        ) VALUES (
            :employee_id, :gross_salary, :transport, :emp_pension, :emp_rama, :emp_maternity, :emp_cbhi, :total_deductions,
            :employer_pension, :employer_occupational, :employer_rama, :employer_maternity, :employer_cbhi, :total_employer_contribution,
            :net_salary, :month)");

        $stmt->execute([
            'employee_id' => $employeeId,
            'gross_salary' => $D,
            'transport' => $E,
            'emp_pension' => $F,
            'emp_rama' => $PAYE,
            'emp_maternity' => $MAT_EMP,
            'emp_cbhi' => $CBHI_EMP,
            'total_deductions' => $totalDeduction,
            'employer_pension' => $G,
            'employer_occupational' => $H,
            'employer_rama' => $RAMA_EMPLOYER,
            'employer_maternity' => $MAT_EMPLOYER,
            'employer_cbhi' => $CBHI_EMPLOYER,
            'total_employer_contribution' => $totalEmployer,
            'net_salary' => $net,
            'month' => $month
        ]);

$sys= new LoanSystem($this->pdo);
$netaccount = 10;
//$netaccount =$netaccount['id'];

$pension =11;;
//$pension=$pension['id'];

$maternity =12;
//$tpr =103;
$tpr =103;

//$maternity =$maternity['id'];

$mutuel =13;
//$mutuel=$mutuel['id'];

$credit =$sys->getAccountDetails('41100');
$credit =$credit['id'];


$ref ='RCEF-'.date('Ymdhis');
$header = [
    'transaction_date' => date('Y-m-d'),
    'reference' => $ref,
    'description' => 'Salaries payment',
    'created_by' => $_SESSION['user_id']
];

$lines = [];

                $lines[] = [
                    'account_id' => $netaccount,
                    'debit' => $net,
                    'credit' => 0
                ];
                $lines[] = [
                    'account_id' => $pension,
                    'debit' => $G + $F,
                    'credit' => 0
                ];
                $lines[] = [
                    'account_id' => $tpr,
                    'debit' => $PAYE,
                    'credit' => 0
                ];

                $lines[] = [
                    'account_id' => $maternity,
                    'debit' => $MAT_EMP + $MAT_EMPLOYER,
                    'credit' => 0
                ];

                $lines[] = [
                    'account_id' => $mutuel,
                    'debit' => $CBHI_EMP,
                    'credit' => 0
                ];



                $lines[] = [
                    'account_id' => $credit,
                    'debit' => 0,
                    'credit' => $net + $G + $F + $MAT_EMPLOYER + $CBHI_EMP +  $MAT_EMP +  $PAYE
                ];


                $tm = new AccountingSystem($this->pdo);
                $transactionId = $tm->createJournalEntry($header, $lines);
                $tm->postJournalEntry($transactionId, $_SESSION['user_id']);



    }
    public function createUserFromEmployee(array $data): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password, email, role, created_at, is_active)
            VALUES (:username, :password, :email, :role, NOW(), :is_active)
        ");
        $stmt->execute([
            'username' => $data['email'],
            'password' => $data['password'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'is_active'=> $data['is_active']
        ]);
    }



    public function assignRoles(int $employeeId, array $roleIds): void {
        $this->pdo->prepare("DELETE FROM employee_roles WHERE employee_id = ?")->execute([$employeeId]);
        $stmt = $this->pdo->prepare("INSERT INTO employee_roles (employee_id, role_id) VALUES (?, ?)");
        foreach ($roleIds as $roleId) {
            $stmt->execute([$employeeId, $roleId]);
        }
    }


    public function getEmployeeById(int $employeeId): ?array {
    $stmt = $this->pdo->prepare("
        SELECT e.* FROM employees e WHERE e.id = :id
    ");
    $stmt->execute(['id' => $employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    return $employee ?: null;
}



public function getEmployeeIdByUserId($userId)
{
    $stmt = $this->pdo->prepare("SELECT id FROM employees WHERE email = :email");
    $stmt->execute(['email' => $userId]);
    $employee = $stmt->fetch();
    return $employee ? $employee['id'] : null;
}




public function getEmployeePermissions($employeeId) {
    $sql = "SELECT p.name AS permission_name
            FROM employee_roles er
            JOIN role_permissions rp ON er.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE er.employee_id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$employeeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




    public function getEmployeeRoles(int $employeeId): array {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.role_name
            FROM roles r
            JOIN employee_roles er ON r.id = er.role_id
            WHERE er.employee_id = ?
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addReduction(int $employeeId, array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO reductions (employee_id, tax, pension, contribution, notes)
                                     VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $employeeId, $data['tax'], $data['pension'], $data['contribution'], $data['notes'] ?? null
        ]);
    }

    public function getDepartments(): array {
        $stmt = $this->pdo->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllRoles(): array {
        $stmt = $this->pdo->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function processPayroll(int $employeeId, float $bonus = 0): bool {
        $employee = $this->pdo->query("SELECT salary FROM employees WHERE id = $employeeId")->fetch();
        $reduction = $this->pdo->query("SELECT tax, pension, contribution FROM reductions
                                        WHERE employee_id = $employeeId ORDER BY id DESC LIMIT 1")->fetch();

        $base = $employee['salary'];
        $deductions = $reduction['tax'] + $reduction['pension'] + $reduction['contribution'];
        $net = $base + $bonus - $deductions;

        $stmt = $this->pdo->prepare("INSERT INTO payroll (employee_id, month, base_salary, bonuses, deductions, net_pay, processed_by)
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $employeeId, date('F Y'), $base, $bonus, $deductions, $net, $_SESSION['user_id'] ?? 0
        ]);
    }
    public function getAllEmployees(): array {
        $stmt = $this->pdo->query("SELECT * FROM employees ORDER BY full_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPayrolls() {
        $stmt = $this->pdo->query("
            SELECT p.*, e.full_name
            FROM payrolls p
            JOIN employees e ON p.employee_id = e.id
            ORDER BY p.month DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}






