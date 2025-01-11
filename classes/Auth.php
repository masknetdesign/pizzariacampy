<?php
class Auth {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($nome, $email, $senha) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->table_name . " (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha_hash);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($email, $senha) {
        $query = "SELECT id, nome, email, senha, tipo FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($senha, $row['senha'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_nome'] = $row['nome'];
                $_SESSION['user_tipo'] = $row['tipo'];
                return true;
            }
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
            return false;
        }
        
        // Verificar diretamente no banco de dados
        $query = "SELECT tipo FROM " . $this->table_name . " 
                 WHERE id = :id AND tipo = 'admin' 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['tipo'] === 'admin';
        }
        
        return false;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return true;
    }

    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $query = "SELECT id, nome, email, tipo FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
