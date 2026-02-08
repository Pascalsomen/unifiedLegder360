<?php

class Supplier
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Method to get all suppliers
    public function getAllSuppliers()
    {
        $stmt = $this->pdo->prepare("SELECT id, name FROM suppliers  ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to get a supplier by ID
    public function getSupplierById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = :id ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Method to create a new supplier
    public function createSupplier($name, $contact_person, $email, $phone, $address)
    {
        $stmt = $this->pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, is_active)
            VALUES (:name, :contact_person, :email, :phone, :address, 1)");

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact_person', $contact_person);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }

        return false;
    }

    // Method to update an existing supplier
    public function updateSupplier($id, $name, $contact_person, $email, $phone, $address)
    {
        $stmt = $this->pdo->prepare("UPDATE suppliers SET name = :name, contact_person = :contact_person,
            email = :email, phone = :phone, address = :address WHERE id = :id");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact_person', $contact_person);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        return $stmt->execute();
    }

    // Method to deactivate a supplier
    public function deactivateSupplier($id)
    {
        $stmt = $this->pdo->prepare("UPDATE suppliers SET is_active = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>
