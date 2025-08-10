<?php
 /**
  * Configuração de conexão com o banco de dados
  * LJ-OS Sistema para Lava Jato
  */

 // Configurações do banco de dados
 define('DB_HOST', 'localhost');
 define('DB_NAME', 'lava_jato_db');
 define('DB_USER', 'root');
 define('DB_PASS', '');
 define('DB_CHARSET', 'utf8mb4');

 /**
  * Classe para gerenciar conexões com o banco de dados
  */
 class Database {
     private static $instance = null;
     private $connection;
     
     private function __construct() {
         try {
             $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
             $options = [
                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false,
                 PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
             ];
             $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
         } catch (PDOException $e) {
             error_log("Erro de conexão com banco de dados: " . $e->getMessage());
             die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
         }
     }
     public static function getInstance() {
         if (self::$instance === null) {
             self::$instance = new self();
         }
         return self::$instance;
     }
     public function getConnection() {
         return $this->connection;
     }
     private function __clone() {}
     public function __wakeup() {}
 }
 function getDB() {
     return Database::getInstance()->getConnection();
 }
?>