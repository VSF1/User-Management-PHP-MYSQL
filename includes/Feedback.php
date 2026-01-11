<?php
class Feedback
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function sendFeedback($sender, $receiver, $title, $description, $attachment)
    {
        $sql = "INSERT INTO feedback (sender, receiver, title, feedbackdata, attachment) VALUES (:sender, :receiver, :title, :description, :attachment)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sender', $sender, PDO::PARAM_STR);
        $stmt->bindParam(':receiver', $receiver, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':attachment', $attachment, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getFeedbackByReceiver($receiver, $limit = null, $offset = null, $search = '', $sortBy = 'id', $sortOrder = 'DESC')
    {
        $allowedSortColumns = ['id', 'sender', 'title', 'feedbackdata'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM feedback WHERE receiver = :receiver";
        if (!empty($search)) {
            $sql .= " AND (LOWER(sender) LIKE LOWER(:search) OR LOWER(title) LIKE LOWER(:search) OR LOWER(feedbackdata) LIKE LOWER(:search))";
        }
        $sql .= " ORDER BY " . $sortBy . " " . $sortOrder;
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':receiver', $receiver, PDO::PARAM_STR);
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

    public function getTotalFeedbackCount($receiver, $search = '')
    {
        $sql = "SELECT COUNT(*) as count FROM feedback WHERE receiver = :receiver";
        if (!empty($search)) {
            $sql .= " AND (LOWER(sender) LIKE LOWER(:search) OR LOWER(title) LIKE LOWER(:search) OR LOWER(feedbackdata) LIKE LOWER(:search))";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':receiver', $receiver, PDO::PARAM_STR);
        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    public function markAsRead($id)
    {
        $sql = "UPDATE feedback SET is_read = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function markAllAsRead($receiver)
    {
        $sql = "UPDATE feedback SET is_read = 1 WHERE receiver = :receiver AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':receiver', $receiver, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getUnreadFeedbackCount($receiver)
    {
        $sql = "SELECT COUNT(*) as count FROM feedback WHERE receiver = :receiver AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':receiver', $receiver, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    public function getConversation($user1, $user2)
    {
        $sql = "SELECT * FROM feedback WHERE (sender = :user1 AND receiver = :user2) OR (sender = :user2 AND receiver = :user1) ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user1', $user1, PDO::PARAM_STR);
        $stmt->bindParam(':user2', $user2, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>