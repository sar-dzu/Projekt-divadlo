<?php

namespace Classes;
use PDO;
use Classes\Database;
class Formular
{
    private PDO $conn;
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }
    public function saveMessage($meno, $email, $predmet, $sprava)
    {
        $sql = "INSERT INTO kontaktne_spravy (meno, email, predmet, sprava)
            VALUES (:meno, :email, :predmet, :sprava)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':meno' => $meno,
            ':email' => $email,
            ':predmet' => $predmet,
            ':sprava' => $sprava
        ]);
    }
    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM kontaktne_spravy ORDER BY datum DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM kontaktne_spravy WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM kontaktne_spravy WHERE id = ?");
        return $stmt->execute([$id]);
    }
}