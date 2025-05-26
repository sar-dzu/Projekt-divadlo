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
    public function getObrazkyByHraId($hraId) {
        $sql = "SELECT obrazok FROM hra_obrazky WHERE hra_id = :id ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $hraId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

    public function getHraCount() {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM predstavenia");
        return $stmt->fetchColumn();
    }

    public function getHryLimit($offset, $limit) {
        $stmt = $this->conn->prepare("SELECT * FROM predstavenia ORDER BY nazov ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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