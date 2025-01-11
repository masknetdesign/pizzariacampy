<?php
session_start();
require_once "../config/database.php";
require_once "../classes/Auth.php";

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Verificar se é admin
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

// Pegar timestamp da última verificação da sessão
$lastCheck = isset($_SESSION['last_order_check']) ? $_SESSION['last_order_check'] : date('Y-m-d H:i:s', strtotime('-1 minute'));

// Buscar pedidos novos desde a última verificação
$query = "SELECT COUNT(*) as count FROM pedidos WHERE created_at > :last_check AND status = 'recebido'";
$stmt = $db->prepare($query);
$stmt->bindParam(':last_check', $lastCheck);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Atualizar timestamp da última verificação
$_SESSION['last_order_check'] = date('Y-m-d H:i:s');

echo json_encode([
    'hasNewOrders' => $result['count'] > 0,
    'count' => $result['count']
]);
