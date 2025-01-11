<?php
// Diretórios necessários
$directories = [
    'uploads',
    'uploads/produtos',
    'uploads/categorias'
];

// Criar diretórios se não existirem
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            echo "Diretório criado: $dir\n";
        } else {
            echo "Erro ao criar diretório: $dir\n";
        }
    } else {
        echo "Diretório já existe: $dir\n";
    }
    
    // Garantir permissões corretas
    chmod($path, 0777);
}
