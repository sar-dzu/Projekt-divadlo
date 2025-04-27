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

    public function getAll(): array{
        $sql = "SELECT * FROM predstavenia";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, string $nazov, string $popis, string $zaciatok, string $koniec, int $trvanie, int $vekObmedzenie): bool {
        $sql = "UPDATE predstavenia
        SET nazov = :nazov, popis = :popis,
            zaciatok_hrania = :zaciatok, koniec_hrania = :koniec,
            trvanie = :trvanie, vekove_obmedzenie = :vekObmedzenie
        WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nazov' => $nazov,
            'popis' => $popis,
            'zaciatok' => $zaciatok,
            'koniec' => $koniec,
            'trvanie' => $trvanie,
            'vekObmedzenie' => $vekObmedzenie
        ]);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM predstavenia WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function addImage(int $hraId, string $obrazok): bool {
        $sql = "INSERT INTO hra_obrazky (hra_id, obrazok) VALUES (:hra_id, :obrazok)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'hra_id' => $hraId,
            'obrazok' => $obrazok
        ]);
    }

    public function getLastInsertedId(): int {
        return $this->lastInsertedId;
    }

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
}