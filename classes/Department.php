<?php
class Department {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new department
     */
    public function createDepartment(string $name, string $code, ?int $managerId = null, ?int $parentId = null): int {
        $this->validateDepartment($name, $code);

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO departments (name, code, manager_id, parent_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $code, $managerId, $parentId]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new Exception("Department code already exists");
            }
            throw new Exception("Failed to create department: " . $e->getMessage());
        }
    }

    /**
     * Update a department
     */
    public function updateDepartment(int $id, string $name, string $code, ?int $managerId = null, ?int $parentId = null): bool {
        $this->validateDepartment($name, $code, $id);

        try {
            $stmt = $this->pdo->prepare("
                UPDATE departments
                SET name = ?, code = ?, manager_id = ?, parent_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $code, $managerId, $parentId, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new Exception("Department code already exists");
            }
            throw new Exception("Failed to update department: " . $e->getMessage());
        }
    }

    /**
     * Get department by ID
     */
    public function getDepartment(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT d.*,
                   CONCAT(u.full_name, ' ', u.full_name) AS manager_name,
                   pd.name AS parent_department_name
            FROM departments d
            LEFT JOIN users u ON d.manager_id = u.id
            LEFT JOIN departments pd ON d.parent_id = pd.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all departments
     */
    public function getAllDepartments(bool $activeOnly = true): array {
        $sql = "
            SELECT d.*,
                   CONCAT(u.full_name, ' ', u.full_name) AS manager_name,
                   pd.name AS parent_department_name
            FROM departments d
            LEFT JOIN users u ON d.manager_id = u.id
            LEFT JOIN departments pd ON d.parent_id = pd.id
        ";

        if ($activeOnly) {
            $sql .= " WHERE d.is_active = TRUE";
        }

        $sql .= " ORDER BY d.name";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get department tree hierarchy
     */
    public function getDepartmentTree(): array {
        $departments = $this->getAllDepartments();
        return $this->buildTree($departments);
    }

    /**
     * Toggle department active status
     */
    public function toggleDepartmentStatus(int $id, bool $isActive): bool {
        $stmt = $this->pdo->prepare("
            UPDATE departments
            SET is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$isActive, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Validate department data
     */
    private function validateDepartment(string $name, string $code, ?int $excludeId = null): void {
        if (empty($name)) {
            throw new Exception("Department name is required");
        }

        if (empty($code)) {
            throw new Exception("Department code is required");
        }

        // Check for duplicate code
        $sql = "SELECT COUNT(*) FROM departments WHERE code = ?";
        $params = [$code];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Department code already exists");
        }
    }

    /**
     * Build hierarchical tree from flat department list
     */
    private function buildTree(array $elements, ?int $parentId = null): array {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }
}