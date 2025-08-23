<?php

/**
 * Configurações do projeto LJ-OS
 */

return [
    // Configurações da aplicação
    'app' => [
        'name' => 'LJ-OS',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => $_ENV['APP_DEBUG'] ?? true,
        'timezone' => 'America/Sao_Paulo',
        'locale' => 'pt_BR',
    ],
    
    // Configurações do banco de dados
    'database' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'lj_os',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    // Configurações de log
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
        'path' => __DIR__ . '/../logs',
        'max_files' => 30,
    ],
    
    // Configurações de cache
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'path' => __DIR__ . '/../cache',
        'ttl' => 3600,
    ],
    
    // Configurações de sessão
    'session' => [
        'lifetime' => 120,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
    ],
];
