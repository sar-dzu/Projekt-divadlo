<?php

namespace Classes;
use Classes\Database;
use PDO;

class Repriza
{
    private PDO $conn;
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    public function addRepriza(int $predstavenieId, string $datetime, int $kapacita): bool {
        $stmt = $this->conn->prepare("INSERT INTO reprizy (predstavenie_id, datum_cas, kapacita) 
                                  VALUES (:id, :cas, :kapacita)");
        return $stmt->execute([
            'id' => $predstavenieId,
            'cas' => $datetime,
            'kapacita' => $kapacita
        ]);
    }

    public function deleteRepriza(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM reprizy WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getReprizy($predstavenieId)
    {
        $sql = "SELECT * FROM reprizy WHERE predstavenie_id = :id AND datum_cas >= NOW() ORDER BY datum_cas ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $predstavenieId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingReprizy($limit = 6)
    {
        $sql = "
        SELECT r.*, p.nazov, p.vekove_obmedzenie, p.trvanie, p.id AS predstavenie_id,
               (SELECT obrazok FROM hra_obrazky WHERE hra_id = p.id ORDER BY RAND() LIMIT 1) AS obrazok
        FROM reprizy r
        JOIN predstavenia p ON r.predstavenie_id = p.id
        WHERE r.datum_cas >= NOW()
        ORDER BY r.datum_cas ASC
        LIMIT :limit
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingUniqueReprizy($limit = 5)
    {
        $sql = "
        SELECT 
            r.predstavenie_id, 
            MIN(r.datum_cas) AS najblizsia_repriza, 
            p.nazov, 
            (
                SELECT ho.obrazok 
                FROM hra_obrazky ho 
                WHERE ho.hra_id = p.id 
                ORDER BY ho.id ASC 
                LIMIT 1
            ) AS obrazok
        FROM reprizy r
        JOIN predstavenia p ON r.predstavenie_id = p.id
        WHERE r.datum_cas >= NOW()
        GROUP BY r.predstavenie_id
        ORDER BY najblizsia_repriza ASC
        LIMIT :limit
    ";

        $stmt = $this->conn->prepare($sql); // použiješ PDO spojenie z konštruktora
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buyTicket($reprizaId): bool {
        // Najprv získať kapacitu
        $sql = "SELECT kapacita FROM reprizy WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $reprizaId]);
        $repriza = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$repriza) {
            return false; // repríza neexistuje
        }

        if ($repriza['kapacita'] <= 0) {
            return false; // kapacita vyčerpaná
        }

        // Znížiť kapacitu o 1
        $sql = "UPDATE reprizy SET kapacita = kapacita - 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $reprizaId]);
    }

}