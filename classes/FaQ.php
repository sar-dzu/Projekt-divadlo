<?php
namespace Classes;
use PDO;
use classes\Database;

class Faq
{
    private PDO $conn;
    public function __construct(\Classes\Database $database){
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM faq ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
