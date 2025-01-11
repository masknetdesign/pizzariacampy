<?php
class Categoria {
    private $conn;
    private $table_name = "categorias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarTodas() {
        $sql = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function criar($dados) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (nome, descricao, ativo) 
                VALUES 
                (:nome, :descricao, :ativo)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Limpar e validar dados
        $nome = htmlspecialchars(strip_tags($dados['nome']));
        $descricao = htmlspecialchars(strip_tags($dados['descricao']));
        $ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        
        // Bind dos parâmetros
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":ativo", $ativo);
        
        return $stmt->execute();
    }

    public function atualizar($dados) {
        $campos = [];
        $valores = [];
        
        // Campos que podem ser atualizados
        $campos_permitidos = ['nome', 'descricao', 'ativo'];
        
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
            if ($campo === 'ativo') {
                $stmt->bindValue(":$campo", intval($valor));
            } else {
                $stmt->bindValue(":$campo", htmlspecialchars(strip_tags($valor)));
            }
        }
        
        return $stmt->execute();
    }

    public function excluir($id) {
        // Primeiro verifica se a categoria tem produtos
        $sql = "SELECT COUNT(*) FROM produtos WHERE categoria_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // Se existirem produtos, apenas inativa
            $sql = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = :id";
        } else {
            // Se não existirem produtos, exclui
            $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function buscarCategoria($id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
