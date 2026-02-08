<?php class BudgetingSystem {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createProject($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (name, description, budgeted_amount, revised_budget)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['budgeted_amount'],
            $data['revised_budget']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function addActivity($projectId, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO project_activities (project_id, name, budgeted_amount,revised_amount,actual_expense)
            VALUES (?, ?, ?, ?,?)
        ");
        $stmt->execute([
            $projectId,
            $data['name'],
            $data['budgeted_amount'],
            $data['revised_amount'],
            $data['actual_expense']
        ]);
    }



    public function getActivity($activityId) {
    $stmt = $this->pdo->prepare("SELECT * FROM project_activities WHERE id = ?");
    $stmt->execute([$activityId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function updateActivity($activityId, $data) {
    $stmt = $this->pdo->prepare("
        UPDATE project_activities
        SET name = ?, budgeted_amount = ?, revised_amount = ?, actual_expense = ?
        WHERE id = ?
    ");
    return $stmt->execute([
        $data['name'],
        $data['budgeted_amount'],
        $data['revised_amount'],
        $data['actual_expense'],
        $activityId
    ]);
}

public function getAllProjectSummaries(): array
{
    $stmt = $this->pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
    $projects = $stmt->fetchAll();

    foreach ($projects as &$project) {
        $projectId = $project['id'];

        // Fetch related activities
        $activityStmt = $this->pdo->prepare("SELECT budgeted_amount, revised_amount, actual_expense FROM project_activities WHERE project_id = ?");
        $activityStmt->execute([$projectId]);
        $activities = $activityStmt->fetchAll();

        // Sum values
        $budget = array_sum(array_column($activities, 'budgeted_amount'));
        $revised = array_sum(array_column($activities, 'revised_amount'));
        $actual = array_sum(array_column($activities, 'actual_expense'));

        // Final budget to use in calculations
        $final_budget = ($revised > 0) ? $revised : $budget;
        $percent_used = $final_budget > 0 ? round(($actual / $final_budget) * 100, 2) : 0;

        // Attach computed values
        $project['budgeted_amount'] = $budget;
        $project['revised_budget'] = $revised;
        $project['actual_expense_total'] = $actual;
        $project['percent_used'] = $percent_used;
    }

    return $projects;
}

    public function getProjectDetails($projectId) {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();

        $activities = $this->pdo->prepare("SELECT * FROM project_activities WHERE project_id = ?");
        $activities->execute([$projectId]);
        $project['activities'] = $activities->fetchAll();

        $project['actual_expense_total'] = array_sum(array_column($project['activities'], 'actual_expense'));
        $budget = array_sum(array_column($project['activities'], 'budgeted_amount'));
        $rivised = array_sum(array_column($project['activities'], 'revised_amount'));
        $project['budgeted_amount']= $budget;
        $project['revised_budget']=  $rivised;
       $final_budget = ($rivised > 0) ? $rivised : $budget;

$project['balance'] = $final_budget - $project['actual_expense_total'];
$project['percent_used'] = $final_budget > 0 ? round(($project['actual_expense_total'] / $final_budget) * 100, 2) : 0;

        return $project;
    }

    public function getAllProjectsWithStats() {
        // Fetch all projects
        $stmt = $this->pdo->query("SELECT * FROM projects");
        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            // Fetch activities for each project
            $stmt = $this->pdo->prepare("SELECT * FROM project_activities WHERE project_id = :project_id");
            $stmt->execute(['project_id' => $project['id']]);
            $activities = $stmt->fetchAll();

            // Calculate budget and expenses stats
            $totalBudgeted = array_sum(array_column($activities, 'budgeted_amount'));
            $totalActual = array_sum(array_column($activities, 'actual_expense'));
            $balance = $totalBudgeted - $totalActual;
            $percentUsed = $totalBudgeted > 0 ? ($totalActual / $totalBudgeted) * 100 : 0;

            // Add stats to the project
            $project['activities'] = $activities;
            $project['budgeted_amount'] = $totalBudgeted;
            $project['actual_expense_total'] = $totalActual;
            $project['balance'] = $balance;
            $project['percent_used'] = number_format($percentUsed, 2);
        }

        return $projects;
    }




}
?>