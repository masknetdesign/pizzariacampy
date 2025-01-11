<?php
class Pedido {
    private $conn;
    private $table_name = "pedidos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criar($dados) {
        try {
            $this->conn->beginTransaction();
            
            // Inserir pedido
            $stmt = $this->conn->prepare("
                INSERT INTO pedidos (
                    usuario_id, 
                    endereco_rua, 
                    endereco_numero, 
                    endereco_complemento, 
                    endereco_bairro, 
                    endereco_referencia,
                    forma_pagamento,
                    troco_para,
                    valor_total,
                    status
                ) VALUES (
                    :usuario_id,
                    :rua,
                    :numero,
                    :complemento,
                    :bairro,
                    :referencia,
                    :forma_pagamento,
                    :troco_para,
                    :valor_total,
                    'pendente'
                )
            ");
            
            $stmt->execute([
                ':usuario_id' => $dados['usuario_id'],
                ':rua' => $dados['rua'],
                ':numero' => $dados['numero'],
                ':complemento' => $dados['complemento'] ?? '',
                ':bairro' => $dados['bairro'],
                ':referencia' => $dados['referencia'] ?? '',
                ':forma_pagamento' => $dados['forma_pagamento'],
                ':troco_para' => $dados['troco'] ?? null,
                ':valor_total' => $dados['valor_total']
            ]);
            
            $pedido_id = $this->conn->lastInsertId();
            
            // Inserir itens do pedido
            $stmt = $this->conn->prepare("
                INSERT INTO pedido_itens (
                    pedido_id,
                    produto_id,
                    tamanho_id,
                    borda_id,
                    quantidade,
                    preco_unitario,
                    observacoes
                ) VALUES (
                    :pedido_id,
                    :produto_id,
                    :tamanho_id,
                    :borda_id,
                    :quantidade,
                    :preco_unitario,
                    :observacoes
                )
            ");
            
            foreach ($dados['itens'] as $item) {
                $stmt->execute([
                    ':pedido_id' => $pedido_id,
                    ':produto_id' => $item['produto_id'],
                    ':tamanho_id' => $item['tamanho_id'] ?? null,
                    ':borda_id' => $item['borda_id'] ?? null,
                    ':quantidade' => $item['quantidade'],
                    ':preco_unitario' => $item['preco_unitario'],
                    ':observacoes' => $item['observacoes'] ?? ''
                ]);
            }
            
            $this->conn->commit();
            return $pedido_id;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao criar pedido: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPedido($id) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.*,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM pedidos p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarItensPedido($pedido_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                pi.*,
                p.nome as produto_nome,
                t.nome as tamanho_nome,
                b.nome as borda_nome
            FROM pedido_itens pi
            JOIN produtos p ON p.id = pi.produto_id
            LEFT JOIN tamanhos t ON t.id = pi.tamanho_id
            LEFT JOIN bordas b ON b.id = pi.borda_id
            WHERE pi.pedido_id = ?
        ");
        
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarStatus($pedido_id, $novo_status) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE pedidos 
                SET status = :status
                WHERE id = :pedido_id
            ");
            
            return $stmt->execute([
                ':status' => $novo_status,
                ':pedido_id' => $pedido_id
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }

    public function listarPedidosUsuario($usuario_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.*,
                COUNT(pi.id) as total_itens
            FROM pedidos p
            LEFT JOIN pedido_itens pi ON pi.pedido_id = p.id
            WHERE p.usuario_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPedidos($status = null, $limit = 50) {
        $sql = "SELECT p.*, u.nome as cliente_nome, u.email as cliente_email
                FROM " . $this->table_name . " p
                LEFT JOIN usuarios u ON p.usuario_id = u.id";
        
        if ($status) {
            $sql .= " WHERE p.status = :status";
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }

    public function getPedidosByUsuario($usuario_id) {
        $sql = "SELECT * FROM " . $this->table_name . " 
                WHERE usuario_id = :usuario_id 
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidoItens($pedido_id) {
        $sql = "SELECT pi.*, p.nome, p.preco as preco_unitario, b.nome as borda_nome, b.preco_adicional
                FROM pedido_itens pi
                LEFT JOIN produtos p ON pi.produto_id = p.id
                LEFT JOIN bordas b ON pi.borda_id = b.id
                WHERE pi.pedido_id = :pedido_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":pedido_id", $pedido_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstatisticas() {
        $stats = [];
        
        // Total de pedidos por status
        $sql = "SELECT status, COUNT(*) as total FROM " . $this->table_name . " GROUP BY status";
        $stmt = $this->conn->query($sql);
        $stats['pedidos_por_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Faturamento total
        $sql = "SELECT SUM(valor_total) as total FROM " . $this->table_name . " WHERE status = 'entregue'";
        $stmt = $this->conn->query($sql);
        $stats['faturamento_total'] = $stmt->fetch(PDO::FETCH_COLUMN);
        
        // Produtos mais vendidos
        $sql = "SELECT p.nome, COUNT(*) as total
                FROM pedido_itens pi
                LEFT JOIN produtos p ON pi.produto_id = p.id
                GROUP BY pi.produto_id
                ORDER BY total DESC
                LIMIT 5";
        $stmt = $this->conn->query($sql);
        $stats['produtos_populares'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }
}
?>
