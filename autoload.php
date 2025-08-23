<?php

/**
 * Autoloader simples para o sistema LJ-OS
 */

spl_autoload_register(function ($class) {
    // Mapear namespace LJOS para diretório src
    if (strpos($class, 'LJOS\\') === 0) {
        // Remover LJOS\ e adicionar src/
        $classPath = substr($class, 5); // Remove 'LJOS\' (5 caracteres)
        $file = __DIR__ . '/src/' . str_replace('\\', '/', $classPath) . '.php';
    } else {
        // Para outras classes, usar o diretório atual
        $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    }
    
    if (file_exists($file)) {
        require_once $file;
    }
});
