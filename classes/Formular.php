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

}