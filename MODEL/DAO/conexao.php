<?php
class Conn
{
    private static $instance = null;
    private $conn;
    
    private $host = "localhost";
    private $dbname = "sgf";
    private $username = "root";
    private $password = "1234";
    
    private function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_FOUND_ROWS => true, // 🔥 FORÇA rowCount() a funcionar
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Não foi possível ligar a base de dados: " . $e->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __wakeup()
    {
        throw new Exception("Não é possível desserializar singleton");
    }
    
    private function __clone() {}
    
    public function getConnection()
    {
        return $this->conn;
    }
    
    public function executar($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // PDO::MYSQL_ATTR_FOUND_ROWS garante que rowCount()
            // retorne o número de linhas AFETADAS (não apenas modificadas)
            $linhasAfetadas = $stmt->rowCount();
            
            // Log para debug (remove depois de testar)
            error_log("SQL executada: $sql | Linhas afetadas: $linhasAfetadas");
            
            return true; // Se chegou aqui, não houve exceção = sucesso
        } catch (PDOException $e) {
            error_log("Erro SQL: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    public function buscarUm($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro SQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function buscarTodos($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro SQL: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function ultimoId()
    {
        return $this->conn->lastInsertId();
    }
}