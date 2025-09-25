<?php

$DB_HOST    = 'db.fr-pari1.bengt.wasmernet.com';            
$DB_NAME    = 'paramount_development';   
$DB_USER    = '4c4349ab7ee780005fa00c4259bd';                 
$DB_PASS    = '068d4c43-49ac-70e6-8000-21098491a8dd';                     
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  exit('DB connection failed: ' . $e->getMessage());
}
