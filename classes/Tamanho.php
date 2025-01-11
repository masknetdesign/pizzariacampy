<?php
class Tamanho {
    private $conn;
    private $table_name = "tamanhos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarTodos() {
        $sql = "SELECT * FROM " . $this->table_name . " ORDER BY multiplicador_preco";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function criar($dados) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (nome, descricao, multiplicador_preco, ativo) 
                VALUES 
                (:nome, :descricao, :multiplicador_preco, :ativo)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Limpar e validar dados
        $nome = htmlspecialchars(strip_tags($dados['nome']));
        $descricao = htmlspecialchars(strip_tags($dados['descricao']));
        $multiplicador_preco = floatval($dados['multiplicador_preco']);
        $ativo = isset($dados['ativo']) ? 1 : 0;
        
        // Bind dos parâmetros
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":multiplicador_preco", $multiplicador_preco);
        $stmt->bindParam(":ativo", $ativo);
        
        return $stmt->execute();
    }

    public function atualizar($dados) {
        $campos = [];
        $valores = [];
        
        // Campos que podem ser atualizados
        $campos_permitidos = ['nome', 'descricao', 'multiplicador_preco', 'ativo'];
        
        foreach ($campos_permitidos as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = :$campo";
                $valores[$campo] = $dados[$campo];
            }
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE " . $this->table_name . " 
                SET " . implode(", ", $campos) . "
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        // Bind do ID
        $stmt->bindParam(":id", $dados['id']);
        
        // Bind dos outros parâmetros
        foreach ($valores as $campo => $valor) {
            if ($campo === 'multiplicador_preco') {
                $stmt->bindValue(":$campo", floatval($valor));
            } elseif ($campo === 'ativo') {
                $stmt->bindValue(":$campo", intval($valor));
            } else {
                $stmt->bindValue(":$campo", htmlspecialchars(strip_tags($valor)));
            }
        }
        
        return $stmt->execute();
    }

    public function excluir($id) {
        // Primeiro verifica se o tamanho está em uso
        $sql = "SELECT COUNT(*) FROM produto_tamanhos WHERE tamanho_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // Se estiver em uso, apenas inativa
            $sql = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = :id";
        } else {
            // Se não estiver em uso, exclui
            $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function buscarTamanho($id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
