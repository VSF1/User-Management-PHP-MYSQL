<?php
class User
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    // Login user
    public function login($email, $password)
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email AND status = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $this->log("SQL statement executed: " . $sql . " for user: " . $email, 'DEBUG');
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if ($user) {
                if (password_verify($password, $user->password) || md5($password) === $user->password) {
                    $this->log("User logged in: " . $email);
                    return $user;
                } else {
                    $this->log("Invalid password for user: " . $email, 'WARNING');
                }
            } else {
                $this->log("User not found: " . $email, 'WARNING');
            }
        } catch (Exception $e) {
            $this->log("Error: " . $e->getMessage(), 'ERROR');
        }
        $this->log("Failed login attempt: " . $email, 'WARNING');
        return false;
    }

    // Create new user
    public function register($name, $email, $password, $gender, $mobile, $designation, $image, $role = 'user')
    {
        // Check if email already exists
        $checkSql = "SELECT email FROM users WHERE email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->fetch(PDO::FETCH_OBJ)) {
            $this->log("Registration failed: Email already exists - " . $email, 'WARNING');
            return false; // Email exists
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $status = 1;

        $sql = "INSERT INTO users(name, email, password, gender, mobile, designation, image, status, role) 
                VALUES(:name, :email, :password, :gender, :mobile, :designation, :image, :status, :role)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $stmt->bindParam(':designation', $designation, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $this->log("New user registered: " . $email);
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Activate user account
    public function activate($id)
    {
        $sql = "UPDATE users SET status = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $this->log("User activated: ID " . $id);
        return $stmt->execute();
    }

    // Deactivate user account
    public function deactivate($id)
    {
        $sql = "UPDATE users SET status = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $this->log("User deactivated: ID " . $id);
        return $stmt->execute();
    }

    // Delete user (and archive to deleteduser table)
    public function delete($id)
    {
        // Get email for archive
        $sqlGet = "SELECT email FROM users WHERE id = :id";
        $stmtGet = $this->db->prepare($sqlGet);
        $stmtGet->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtGet->execute();

        $user = $stmtGet->fetch(PDO::FETCH_OBJ);
        if ($user) {
            
            // Archive
            $sqlArchive = "INSERT INTO deleteduser(email) VALUES(:email)";
            $stmtArchive = $this->db->prepare($sqlArchive);
            $stmtArchive->bindParam(':email', $user->email, PDO::PARAM_STR);
            $stmtArchive->execute();

            // Delete
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $this->log("User deleted: ID " . $id . " (" . $user->email . ")");
            return $stmt->execute();
        }
        return false;
    }

    public function changePassword($email, $currentPassword, $newPassword)
    {
        $sql = "SELECT password FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user) {
            if (password_verify($currentPassword, $user->password) || md5($currentPassword) === $user->password) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password = :password WHERE email = :email";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
                $this->log("Password changed for user: " . $email);
                return $updateStmt->execute();
            }
        }
        $this->log("Password change failed for user: " . $email, 'WARNING');
        return false;
    }

    public function getUser($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updateUser($id, $name, $email, $gender, $mobile, $designation, $image, $role)
    {
        $sql = "UPDATE users SET name = :name, email = :email, gender = :gender, mobile = :mobile, designation = :designation, image = :image, role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $stmt->bindParam(':designation', $designation, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $this->log("User details updated: ID " . $id);
            return true;
        }
        return false;
    }

    public function updateProfile($id, $name, $email, $mobile, $designation, $image)
    {
        $sql = "UPDATE users SET name = :name, email = :email, mobile = :mobile, designation = :designation, image = :image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $stmt->bindParam(':designation', $designation, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $this->log("Profile updated for user: " . $email);
            return true;
        }
        return false;
    }

    public function getUsers($limit = null, $offset = null, $search = '', $sortBy = 'id', $sortOrder = 'ASC')
    {
        $allowedSortColumns = ['id', 'name', 'email', 'gender', 'mobile', 'designation', 'status'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM users";
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR email LIKE :search";
        }
        $sql .= " ORDER BY " . $sortBy . " " . $sortOrder;
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTotalUsers($search = '')
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR email LIKE :search";
        }
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    private function log($message, $level = 'INFO') {
        $logger = new Logger();
        $logger->log($message, $level);
    }
}
?>