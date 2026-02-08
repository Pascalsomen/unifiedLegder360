<?php
class InventoryCategory {
    private $pdo;
    private $id;
    private $name;
    private $created_at;
    private $updated_at;
    private $is_active;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function isActive() {
        return $this->is_active;
    }

    // Setters with validation
    public function setName($name) {
        if (empty($name)) {
            throw new InvalidArgumentException("Category name cannot be empty");
        }
        $this->name = trim($name);
        return $this;
    }

    public function setIsActive($is_active) {
        $this->is_active = (bool)$is_active;
        return $this;
    }

    // CRUD Operations
    public function create() {
        $sql = "INSERT INTO inventory_categories (name, created_at, updated_at, is_active)
                VALUES (:name, NOW(), NOW(), :is_active)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name' => $this->name,
            ':is_active' => $this->is_active ?? true
        ]);
        $this->id = $this->pdo->lastInsertId();
        return $this->id;
    }

    public function update() {
        if (empty($this->id)) {
            throw new RuntimeException("Cannot update category without ID");
        }

        $sql = "UPDATE inventory_categories
                SET name = :name,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $this->id,
            ':name' => $this->name,
            ':is_active' => $this->is_active
        ]);
    }

    public function delete() {
        if (empty($this->id)) {
            throw new RuntimeException("Cannot delete category without ID");
        }

        // Soft delete (preferred approach)
        $this->is_active = false;
        return $this->update();

        // Alternatively for hard delete:
        // $sql = "DELETE FROM inventory_categories WHERE id = :id";
        // $stmt = $this->pdo->prepare($sql);
        // return $stmt->execute([':id' => $this->id]);
    }

    // Static methods for fetching data
    public static function getById($pdo, $id) {
        $sql = "SELECT * FROM inventory_categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $category = new self($pdo);
        $category->id = $row['id'];
        $category->name = $row['name'];
        $category->created_at = $row['created_at'];
        $category->updated_at = $row['updated_at'];
        $category->is_active = (bool)$row['is_active'];

        return $category;
    }

    public static function getAll($pdo, $active_only = true) {
        $sql = "SELECT * FROM inventory_categories";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countItemsInCategory($pdo, $category_id) {
        $sql = "SELECT COUNT(*) as item_count
                FROM inventory_items
                WHERE category_id = :category_id AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':category_id' => $category_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['item_count'];
    }
}