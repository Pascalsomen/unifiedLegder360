<?php
public function calculateAndSavePayroll($employeeId, $grossSalary, $transport, $month) {
    $D = $grossSalary;
    $E = $transport;

    // Employee Deductions
    $F = $D * 0.06; // Pension
    $PAYE = $D * 0.075;
    $MAT_EMP = $D * 0.003;
    $CBHI_EMP = $D * 0.003;
    $totalDeduction = $F + $PAYE + $MAT_EMP + $CBHI_EMP;

    // Employer Contributions
    $G = $D * 0.06; // Pension
    $H = ($D - $E) * 0.02; // Occupational hazard
    $RAMA_EMPLOYER = $D * 0.075;
    $MAT_EMPLOYER = $D * 0.003;
    $CBHI_EMPLOYER = $D * 0.003;
    $totalEmployer = $G + $H + $RAMA_EMPLOYER + $MAT_EMPLOYER + $CBHI_EMPLOYER;

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
}

?>