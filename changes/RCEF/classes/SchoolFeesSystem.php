<?php
class SchoolFeesSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addStudent(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO students (first_name, last_name, gender, dob, created_at, phone, address, school_name
        , fees_payment, bank_name, 	bank_account, father_name, mother_name, guardian_name,grade) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['gender'],
            $data['dob'],
            $data['phone'],
            $data['address'],
            $data['school_id'],
            $data['fees_payment'],
            $data['bank_name'],
            $data['bank_account'],
            $data['father_name'],
            $data['mother_name'],
            $data['guardian_name'],
            $data['grade']
        ]);

        $studentId = $this->pdo->lastInsertId();
        if($data['sponsor_id']){
            $this->assignSponsor($studentId, $data['sponsor_id']);

        }


        if (!empty($data['documents'])) {
            $this->uploadDocuments($studentId, $data['documents']);
        }


        $_SESSION['toast'] = "Student added successfully.";

         return $studentId;


    }

    public function addSponsor(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO sponsors (name, email, phone,address) VALUES (?, ?, ?,?)");
        $stmt->execute([$data['name'], $data['email'], $data['phone'],$data['address']]);
        return $this->pdo->lastInsertId();
    }

    public function assignSponsor(int $studentId, int $sponsorId): bool {
        $stmt = $this->pdo->prepare("INSERT INTO student_sponsor (student_id, sponsor_id) VALUES (?, ?)");
        return $stmt->execute([$studentId, $sponsorId]);
    }

    public function addTerm(array $data): bool|string {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO terms (term_name, year, start_date, end_date, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $data['term_name'],
                $data['year'],
                $data['start_date'],
                $data['end_date'],
            ]);

             $_SESSION['toast'] =  "Term Successfull added";
            return $this->pdo->lastInsertId(); // Return ID on success
        } catch (PDOException $e) {
            // Log or echo the error for debugging
            $_SESSION['error'] =  "Error inserting term: " . $e->getMessage();
            return false;
        }
    }


    public function addPayment(array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO fees_payments (student_id, amount, term_id, payment_date, method, reference) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['student_id'], $data['amount'], $data['term_id'], $data['payment_date'], $data['method'], $data['reference']]);
    }

  public function uploadDocuments($studentId, $files)
{
    foreach ($files as $file) {
        $stmt = $this->pdo->prepare("
            INSERT INTO student_documents (student_id, document_name, filetype, uploaded_at)
            VALUES (:student_id, :filename, :filetype, NOW())
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'filename' => $file['filename'],
            'filetype' => $file['filetype']
        ]);
    }
}



public function listStudents(): array {
    $stmt = $this->pdo->query("
        SELECT
            s.*,
            sd.filepath AS profile_picture
        FROM students s
        LEFT JOIN (
            SELECT sd1.*
            FROM student_documents sd1
            JOIN (
                SELECT student_id, MAX(id) AS max_id
                FROM student_documents
                WHERE document_name = 'picture'
                GROUP BY student_id
            ) latest ON sd1.id = latest.max_id
        ) sd ON s.id = sd.student_id
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// public function getSponsorById($id)
// {
//     $stmt = $this->pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
//     $stmt->execute([$id]);
//     return $stmt->fetch(PDO::FETCH_ASSOC);
// }

public function getStudentsBySponsor($sponsorId)
{
    $stmt = $this->pdo->prepare("
        SELECT s.*
        FROM students s
        JOIN student_sponsor ss ON s.id = ss.student_id
        WHERE ss.sponsor_id = ?
    ");
    $stmt->execute([$sponsorId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getStudentById($id)
{
    $stmt = $this->pdo->prepare("
        SELECT
            s.*,
            sp.name AS sponsor_name,
            sd.filepath AS profile_picture
        FROM students s
        LEFT JOIN student_sponsor ss ON s.id = ss.student_id
        LEFT JOIN sponsors sp ON ss.sponsor_id = sp.id
        LEFT JOIN (
            SELECT sd1.*
            FROM student_documents sd1
            JOIN (
                SELECT student_id, MAX(id) AS max_id
                FROM student_documents
                WHERE document_name = 'picture'
                GROUP BY student_id
            ) latest ON sd1.id = latest.max_id
        ) sd ON s.id = sd.student_id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    public function getStudentDocuments($studentId)
{
    $stmt = $this->pdo->prepare("SELECT * FROM student_documents WHERE student_id = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



public function deleteDoument($docId)
{
    if ($docId) {
        // Get file name
        $stmt = $school->pdo->prepare("SELECT filename FROM student_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            $filePath = __DIR__ . '/uploads/student_documents/' . $file['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from DB
            $del = $school->pdo->prepare("DELETE FROM student_documents WHERE id = ?");
            $del->execute([$docId]);
        }
    }
}

public function updateStudent($studentId, $data)
{
    $stmt = $this->pdo->prepare("
        UPDATE students
        SET first_name = ?, last_name = ?, gender = ?, dob = ?, phone = ?, address = ?, school_name = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['gender'],
        $data['dob'],
        $data['phone'],
        $data['address'],
        $data['school_name'],
        $studentId
    ]);

    // Update sponsor relationship
    if (!empty($data['sponsor_id'])) {
        // Check if already exists
        $check = $this->pdo->prepare("SELECT * FROM student_sponsors WHERE student_id = ?");
        $check->execute([$studentId]);

        if ($check->fetch()) {
            $update = $this->pdo->prepare("UPDATE student_sponsors SET sponsor_id = ? WHERE student_id = ?");
            $update->execute([$data['sponsor_id'], $studentId]);
        } else {
            $insert = $this->pdo->prepare("INSERT INTO student_sponsors (student_id, sponsor_id) VALUES (?, ?)");
            $insert->execute([$studentId, $data['sponsor_id']]);
        }
    } else {
        // // Remove sponsor link if blank
        // $delete = $this->pdo->prepare("DELETE FROM student_sponsors WHERE student_id = ?");
        // $delete->execute([$studentId]);
    }
}


public function deleteStudent($studentId)
{
    // Delete student's documents
    $this->pdo->prepare("DELETE FROM student_documents WHERE student_id = ?")->execute([$studentId]);

    // Delete sponsor assignment
    $this->pdo->prepare("DELETE FROM student_sponsor WHERE student_id = ?")->execute([$studentId]);

    // Delete student
    $this->pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$studentId]);
}


    public function getStudentDetails(int $studentId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPayments(int $studentId): array {
        $stmt = $this->pdo->prepare("SELECT f.*, t.name as term_name FROM fees_payments f JOIN academic_terms t ON f.term_id = t.id WHERE f.student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSponsors(): array {
        $stmt = $this->pdo->query("SELECT * FROM sponsors");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSponsorById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSponsor($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE sponsors
            SET name = ?, email = ?, address = ?, phone = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['address'],
            $data['phone'],
            $id
        ]);
    }

    public function getTerms(): array {
        $stmt = $this->pdo->query("SELECT * FROM terms ORDER BY start_date");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSponsors(): array {
        $stmt = $this->pdo->query("SELECT * FROM sponsors ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
