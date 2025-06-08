<?php

namespace Classes;
use Classes\Database;
use PDO;

class Kategoria
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    /*


    KATEGORIE


    */

    public function addCategories(int $hraId, array $kategorie): bool {
        $sql = "INSERT INTO predstavenie_kategoria (predstavenie_id, kategoria_id) VALUES (:hraId, :kategoriaId)";
        $stmt = $this->conn->prepare($sql);
        foreach ($kategorie as $kategoriaId) {
            $stmt->execute([
                'hraId' => $hraId,
                'kategoriaId' => $kategoriaId
            ]);
        }
        return true;
    }

    public function updateCategories($id, $categories) {
        // Najprv vymazať staré kategórie
        $sql = "DELETE FROM predstavenie_kategoria WHERE predstavenie_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Potom pridať nové kategórie
        foreach ($categories as $category_id) {
            $sql = "INSERT INTO predstavenie_kategoria (predstavenie_id, kategoria_id) VALUES (:hra_id, :kategoria_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':hra_id' => $id,
                ':kategoria_id' => $category_id
            ]);
        }
    }

    public function getAllCategories() {
        $sql = "SELECT id, nazov FROM kategorie ORDER BY nazov";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategory($nazovKategorie)
    {
        $sql = "
        SELECT h.*,
               (SELECT obrazok 
                FROM hra_obrazky 
                WHERE hra_id = h.id 
                ORDER BY id ASC 
                LIMIT 1) AS hlavny_obrazok,
               GROUP_CONCAT(k2.nazov SEPARATOR ', ') AS kategorie
        FROM predstavenia h
        JOIN predstavenie_kategoria pk ON h.id = pk.predstavenie_id
        JOIN kategorie k ON k.id = pk.kategoria_id
        -- Pripojenie všetkých kategórií k danej hre (nielen filtrovanej)
        JOIN predstavenie_kategoria pk2 ON h.id = pk2.predstavenie_id
        JOIN kategorie k2 ON k2.id = pk2.kategoria_id
        WHERE k.nazov = :nazov
        GROUP BY h.id
        ORDER BY h.zaciatok_hrania ASC
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nazov' => $nazovKategorie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAssignedCategoryIds(int $hraId): array {
        $sql = "SELECT kategoria_id FROM predstavenie_kategoria WHERE predstavenie_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $hraId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'kategoria_id');
    }

}