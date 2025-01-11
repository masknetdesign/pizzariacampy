<?php
class Produto {
    private $conn;
    private $table_name = "produtos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criar($dados) {
        $sql = "INSERT INTO " . $this->table_name . " 
                (nome, descricao, preco, categoria_id, imagem, ativo) 
                VALUES 
                (:nome, :descricao, :preco, :categoria_id, :imagem, :ativo)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Limpar e validar dados
        $nome = htmlspecialchars(strip_tags($dados['nome']));
        $descricao = htmlspecialchars(strip_tags($dados['descricao']));
        $preco = floatval($dados['preco']);
        $categoria_id = intval($dados['categoria_id']);
        $imagem = isset($dados['imagem']) ? $dados['imagem'] : null;
        $ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        
        // Bind dos parâmetros
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":categoria_id", $categoria_id);
        $stmt->bindParam(":imagem", $imagem);
        $stmt->bindParam(":ativo", $ativo);
        
        return $stmt->execute();
    }

    public function atualizar($dados) {
        $campos = [];
        $valores = [];
        
        // Campos que podem ser atualizados
        $campos_permitidos = ['nome', 'descricao', 'preco', 'categoria_id', 'imagem', 'ativo'];
        
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
            if ($campo === 'preco') {
                $stmt->bindValue(":$campo", floatval($valor));
            } elseif ($campo === 'categoria_id' || $campo === 'ativo') {
                $stmt->bindValue(":$campo", intval($valor));
            } else {
                $stmt->bindValue(":$campo", htmlspecialchars(strip_tags($valor)));
            }
        }
        
        return $stmt->execute();
    }

    public function excluir($id) {
        // Primeiro verifica se o produto está em algum pedido
        $sql = "SELECT COUNT(*) FROM pedido_itens WHERE produto_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // Se existir em pedidos, apenas inativa
            $sql = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = :id";
        } else {
            // Se não existir em pedidos, exclui
            $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function listarPorCategoria($categoria_id = null) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.ativo = true";
        
        if ($categoria_id) {
            $sql .= " AND p.categoria_id = :categoria_id";
        }
        
        $sql .= " ORDER BY c.nome, p.nome";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($categoria_id) {
            $stmt->bindParam(":categoria_id", $categoria_id);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function listarTodos() {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                ORDER BY c.nome, p.nome";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function listarCategorias() {
        $sql = "SELECT * FROM categorias ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function listarBordas() {
        $sql = "SELECT * FROM bordas WHERE ativo = 1 ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    public function buscarProduto($id) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = :id AND p.ativo = true";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            // Buscar preços por tamanho
            $sql = "SELECT t.*, pt.preco as preco_especifico
                    FROM tamanhos t
                    LEFT JOIN produto_tamanhos pt ON pt.tamanho_id = t.id 
                    AND pt.produto_id = :produto_id
                    WHERE t.ativo = 1";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":produto_id", $id);
            $stmt->execute();
            
            $produto['tamanhos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $produto;
    }
}
?>
