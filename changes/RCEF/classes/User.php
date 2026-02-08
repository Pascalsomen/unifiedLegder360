<?php

class User {
    private $pdo;

    // Constructor to initialize the PDO connection
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Create a new user
    public function createUser($username, $password, $role, $email) {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user data into the database
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role, email, last_login, is_active)
                                     VALUES (:username, :password, :role, :email, NOW(), 1)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role,
            ':email' => $email
        ]);

        // Return the ID of the newly created user
        return $this->pdo->lastInsertId();
    }

    // Get user details by ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user information
    public function updateUser($id, $username, $password, $role, $email, $isActive) {
        // Optionally hash password if provided
        $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : null;

        $stmt = $this->pdo->prepare("UPDATE users SET
                                        username = :username,
                                        password = COALESCE(:password, password),
                                        role = :role,
                                        email = :email,
                                        is_active = :is_active
                                      WHERE id = :id");

        $stmt->execute([
            ':id' => $id,
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role,
            ':email' => $email,
            ':is_active' => $isActive
        ]);
    }

    // Get a user by username for login
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verify user password
    public function verifyPassword($inputPassword, $storedPassword) {
        return password_verify($inputPassword, $storedPassword);
    }

    // Update the last login timestamp for a user
    public function updateLastLogin($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    // Check if the user is active
    public function isActive($id) {
        $stmt = $this->pdo->prepare("SELECT is_active FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['is_active'] ?? 0; // returns 0 if no such user is found or inactive
    }

    // Deactivate a user
    public function deactivateUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // Activate a user
    public function activateUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getActiveUsers() {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 1");

    }


    public function getallActiveUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
