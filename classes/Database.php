<?php

declare(strict_types=1);

namespace Classes;
use PDO;
use PDOException;
class Database
{
    private $conn;
    public function __construct() {
        $this->connect();
    }
    protected function connect(): void {
        $config = DATABASE;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );
        try {
            $this->conn = new PDO('mysql:host='.$config['HOST'].';dbname='.$config['DBNAME'].';port='. $config['PORT'], $config['USER_NAME'], $config['PASSWORD'],$options);
        } catch (PDOException $e) {
            die("Chyba pripojenia: ".$e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}