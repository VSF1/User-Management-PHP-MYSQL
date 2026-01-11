<?php
class Notify
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function sendNotification($sender, $receiver, $type)
    {
        $sql = "INSERT INTO notification (notiuser, notireceiver, notitype) VALUES (:notiuser, :notireceiver, :notitype)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':notiuser', $sender, PDO::PARAM_STR);
        $stmt->bindParam(':notireceiver', $receiver, PDO::PARAM_STR);
        $stmt->bindParam(':notitype', $type, PDO::PARAM_STR);
        $logger = new Logger();
        $logger->log("Notification sent from $sender to $receiver: $type");
        return $stmt->execute();
    }

    public function getNotifications($receiver)
    {
        $sql = "SELECT * FROM notification WHERE notireceiver = :reciever ORDER BY time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':reciever', $receiver, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>