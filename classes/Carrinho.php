<?php
class Carrinho {
    private $conn;
    private $table_name = "carrinhos";

    public function __construct($db) {
        $this->conn = $db;
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
    }

    public function adicionar($produto_id, $tamanho_id = null, $borda_id = null, $quantidade = 1, $observacoes = '') {
        try {
            error_log("Iniciando adição ao carrinho: produto_id=$produto_id, tamanho_id=$tamanho_id, borda_id=$borda_id, quantidade=$quantidade");
            
            // Buscar informações do produto
            $sql = "SELECT p.*, c.id as categoria_id 
                   FROM produtos p 
                   LEFT JOIN categorias c ON p.categoria_id = c.id 
                   WHERE p.id = :produto_id AND p.ativo = 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":produto_id", $produto_id);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Produto encontrado: " . print_r($produto, true));
            
            if (!$produto) {
                error_log("Produto não encontrado: $produto_id");
                return false;
            }

            $preco_base = $produto['preco'];
            $preco_borda = 0;
            $tamanho_nome = null;
            $borda_nome = null;

            // Se for pizza, busca informações de tamanho e borda
            if ($produto['categoria_id'] == 1) {
                error_log("Produto é pizza, verificando tamanho e borda");
                
                if (!$tamanho_id) {
                    error_log("Tamanho não especificado para pizza");
                    return false;
                }

                // Verificar se existe preço específico para este tamanho
                $sql = "SELECT t.nome as tamanho_nome, COALESCE(pt.preco, t.multiplicador_preco * :preco_base) as preco
                       FROM tamanhos t
                       LEFT JOIN produto_tamanhos pt ON pt.tamanho_id = t.id AND pt.produto_id = :produto_id
                       WHERE t.id = :tamanho_id AND t.ativo = 1";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(":produto_id", $produto_id);
                $stmt->bindParam(":tamanho_id", $tamanho_id);
                $stmt->bindParam(":preco_base", $produto['preco']);
                $stmt->execute();
                
                $tamanho = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Tamanho encontrado: " . print_r($tamanho, true));
                
                if (!$tamanho) {
                    error_log("Tamanho não encontrado: $tamanho_id");
                    return false;
                }

                $preco_base = $tamanho['preco'];
                $tamanho_nome = $tamanho['tamanho_nome'];

                // Buscar informações da borda se selecionada
                if ($borda_id) {
                    $sql = "SELECT * FROM bordas WHERE id = :borda_id AND ativo = 1";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(":borda_id", $borda_id);
                    $stmt->execute();
                    $borda = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Borda encontrada: " . print_r($borda, true));
                    
                    if ($borda) {
                        $preco_borda = $borda['preco_adicional'];
                        $borda_nome = $borda['nome'];
                    }
                }
            } else {
                error_log("Produto não é pizza, usando preço base");
            }
            
            $preco_unitario = $preco_base + $preco_borda;
            $preco_total = $preco_unitario * $quantidade;

            error_log("Preços calculados: base=$preco_base, borda=$preco_borda, unitario=$preco_unitario, total=$preco_total");

            // Adicionar ao carrinho
            $item = [
                'produto_id' => (int)$produto_id,
                'nome' => $produto['nome'],
                'categoria_id' => (int)$produto['categoria_id'],
                'tamanho_id' => $tamanho_id ? (int)$tamanho_id : null,
                'tamanho_nome' => $tamanho_nome,
                'borda_id' => $borda_id ? (int)$borda_id : null,
                'borda_nome' => $borda_nome,
                'quantidade' => (int)$quantidade,
                'preco_base' => (float)$preco_base,
                'preco_borda' => (float)$preco_borda,
                'preco_unitario' => (float)$preco_unitario,
                'preco_total' => (float)$preco_total,
                'observacoes' => $observacoes
            ];

            error_log("Item preparado para carrinho: " . print_r($item, true));

            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }
            
            $_SESSION['carrinho'][] = $item;
            error_log("Item adicionado ao carrinho com sucesso");
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao adicionar item ao carrinho: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    public function remover($index) {
        if (isset($_SESSION['carrinho'][$index])) {
            unset($_SESSION['carrinho'][$index]);
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']); // Reindexar array
            return true;
        }
        return false;
    }

    public function atualizar($index, $quantidade) {
        if (isset($_SESSION['carrinho'][$index])) {
            $item = &$_SESSION['carrinho'][$index];
            $quantidade = (int)$quantidade;
            $item['quantidade'] = $quantidade;
            $item['preco_total'] = (float)($item['preco_unitario'] * $quantidade);
            return true;
        }
        return false;
    }

    public function getItens() {
        return array_map(function($item) {
            return array_merge($item, [
                'preco_base' => (float)$item['preco_base'],
                'preco_borda' => (float)$item['preco_borda'],
                'preco_unitario' => (float)$item['preco_unitario'],
                'preco_total' => (float)$item['preco_total'],
                'quantidade' => (int)$item['quantidade']
            ]);
        }, $_SESSION['carrinho'] ?? []);
    }

    public function getQuantidadeItens() {
        if (!isset($_SESSION['carrinho'])) {
            return 0;
        }
        
        return array_reduce($_SESSION['carrinho'], function($total, $item) {
            return $total + $item['quantidade'];
        }, 0);
    }

    public function getTotal() {
        return array_reduce($_SESSION['carrinho'] ?? [], function($acc, $item) {
            return $acc + (float)$item['preco_total'];
        }, 0.0);
    }

    public function limpar() {
        $_SESSION['carrinho'] = [];
        return true;
    }
}
