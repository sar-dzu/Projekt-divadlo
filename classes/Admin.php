<?php
namespace Classes;

use Classes\Database;
use PDO;

class Admin {

    private PDO $conn;

    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    public function login(string $email, string $heslo): bool {
        $sql = "SELECT * FROM admini WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($heslo, $admin['heslo'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $admin['email'];
            return true;
        }

        return false;
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public function logout(): void {
        session_unset();
        session_destroy();
    }
}
