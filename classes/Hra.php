<?php

namespace Classes;
use PDO;
use Classes\Database;

class Hra
{
    private PDO $conn;
    private int $lastInsertedId;
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    public function create(string $nazov, string $popis, string $zaciatok, ?string $koniec, int $trvanie, int $vekObmedzenie): bool {
        $sql = "INSERT INTO predstavenia (nazov, popis, zaciatok_hrania, koniec_hrania, trvanie, vekove_obmedzenie)
                VALUES (:nazov, :popis, :zaciatok, :koniec,:trvanie, :vek)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            'nazov' => $nazov,
            'popis' => $popis,
            'zaciatok' => $zaciatok,
            'koniec' => $koniec,
            'trvanie' => $trvanie,
            'vek' => $vekObmedzenie
        ]);
        if ($result) {
            $this->lastInsertedId = (int) $this->conn->lastInsertId();
        }

        return $result;
    }

    public function update(int $id, string $nazov, string $popis, string $zaciatok, ?string $koniec, int $trvanie, int $vekObmedzenie): bool {
        $sql = "UPDATE predstavenia
        SET nazov = :nazov, popis = :popis,
            zaciatok_hrania = :zaciatok, koniec_hrania = :koniec,
            trvanie = :trvanie, vekove_obmedzenie = :vek
        WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nazov' => $nazov,
            'popis' => $popis,
            'zaciatok' => $zaciatok,
            'koniec' => $koniec,
            'trvanie' => $trvanie,
            'vek' => $vekObmedzenie
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("SELECT obrazok FROM hra_obrazky WHERE hra_id = :id");
        $stmt->execute(['id' => $id]);
        $obrazky = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($obrazky as $obrazok) {
            $path = "assets/images/" . $obrazok['obrazok'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $this->conn->prepare("DELETE FROM hra_obrazky WHERE hra_id = :id")->execute(['id' => $id]);
        $this->conn->prepare("DELETE FROM predstavenie_kategoria WHERE predstavenie_id = :id")->execute(['id' => $id]);
        $stmt = $this->conn->prepare("DELETE FROM predstavenia WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getLastInsertedId(): int {
        return $this->lastInsertedId;
    }

    public function getAllOrderedByDateLogic(): array {
        $sql = "
        SELECT 
            p.id, 
            p.nazov, 
            p.popis, 
            p.zaciatok_hrania, 
            p.koniec_hrania, 
            p.trvanie, 
            p.vekove_obmedzenie, 
            (SELECT obrazok FROM hra_obrazky WHERE hra_id = p.id LIMIT 1) AS hlavny_obrazok,
            (
                SELECT GROUP_CONCAT(k.nazov SEPARATOR ', ')
                FROM predstavenie_kategoria pk
                JOIN kategorie k ON pk.kategoria_id = k.id
                WHERE pk.predstavenie_id = p.id
            ) AS kategorie,
            NULL AS triedenie
        FROM predstavenia p
        ORDER BY 
            CASE 
                WHEN p.koniec_hrania IS NOT NULL THEN p.koniec_hrania
                ELSE p.zaciatok_hrania
            END DESC
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHraById($id)
    {
        $sql = "
        SELECT 
            p.*, 
            (
                SELECT obrazok 
                FROM hra_obrazky 
                WHERE hra_id = p.id 
                ORDER BY id ASC 
                LIMIT 1
            ) AS hlavny_obrazok,
            (
                SELECT GROUP_CONCAT(k.nazov SEPARATOR ', ')
                FROM predstavenie_kategoria pk
                JOIN kategorie k ON pk.kategoria_id = k.id
                WHERE pk.predstavenie_id = p.id
            ) AS kategorie
        FROM predstavenia p
        WHERE p.id = :id
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOldestYear(): ?int
    {
        $sql = "SELECT MIN(zaciatok_hrania) AS najstarsie FROM predstavenia";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch();

        if ($result && $result['najstarsie']) {
            return (int)date('Y', strtotime($result['najstarsie']));
        }

        return null;
    }

    public function getLatestPredstavenie()
    {
        $sql = "
        SELECT 
            p.*, 
            (
                SELECT obrazok 
                FROM hra_obrazky 
                WHERE hra_id = p.id 
                ORDER BY id ASC 
                LIMIT 1
            ) AS hlavny_obrazok
        FROM predstavenia p
        WHERE p.zaciatok_hrania = (
            SELECT MAX(zaciatok_hrania) FROM predstavenia
        )
        LIMIT 1
    ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecommendedHra($excludeId)
    {
        $sql = "SELECT h.*, r.datum_cas,
                   (SELECT obrazok FROM hra_obrazky WHERE hra_id = h.id ORDER BY id ASC LIMIT 1) AS hlavny_obrazok
            FROM predstavenia h
            JOIN reprizy r ON h.id = r.predstavenie_id
            WHERE h.id != :excludeId AND r.datum_cas > NOW()
            ORDER BY r.datum_cas ASC
            LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['excludeId' => $excludeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}