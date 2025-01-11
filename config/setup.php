<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Ler o SQL
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Dividir o SQL em comandos individuais
    $commands = array_filter(
        array_map(
            'trim',
            explode(';', $sql)
        )
    );
    
    // Executar cada comando separadamente
    foreach ($commands as $command) {
        if (!empty($command)) {
            try {
                $db->exec($command);
                echo "Comando executado com sucesso: " . substr($command, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "Erro ao executar comando: " . substr($command, 0, 50) . "...\n";
                echo "Erro: " . $e->getMessage() . "\n";
                // Continuar mesmo se houver erro
            }
        }
    }
    
    echo "\nBanco de dados configurado com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
} catch (Exception $e) {
    die("Erro geral: " . $e->getMessage());
}
