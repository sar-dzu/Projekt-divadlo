<?php

namespace Classes;
use Classes\Database;
use PDO;

class Obrazok
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    /*


    OBRAZOK


    */

    public function addImage(int $hraId, string $obrazok): bool {
        $sql = "INSERT INTO hra_obrazky (hra_id, obrazok) VALUES (:hra_id, :obrazok)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'hra_id' => $hraId,
            'obrazok' => $obrazok
        ]);
    }

    public function getObrazkyByHraId($hraId) {
        $sql = "SELECT obrazok FROM hra_obrazky WHERE hra_id = :id ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $hraId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getObrazkyWithIdsByHraId(int $hraId): array {
        $sql = "SELECT id, obrazok FROM hra_obrazky WHERE hra_id = :id ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $hraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteImageById(int $obrazokId, int $hraId): bool {
        $stmt = $this->conn->prepare("SELECT obrazok FROM hra_obrazky WHERE id = :id AND hra_id = :hra_id");
        $stmt->execute(['id' => $obrazokId, 'hra_id' => $hraId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $filePath = "assets/images/" . $data['obrazok'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $deleteStmt = $this->conn->prepare("DELETE FROM hra_obrazky WHERE id = :id");
            return $deleteStmt->execute(['id' => $obrazokId]);
        }

        return false;
    }

}