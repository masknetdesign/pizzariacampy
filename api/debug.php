<?php
// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para log de erros
function logError($message, $data = null) {
    $error = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data
    ];
    
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    file_put_contents(
        $log_file, 
        json_encode($error, JSON_PRETTY_PRINT) . "\n", 
        FILE_APPEND
    );
}

// Registrar handler de erros
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError($errstr, [
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ]);
});

// Registrar handler de exceções
set_exception_handler(function($e) {
    logError($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
});
