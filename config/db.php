<?php

$host = "localhost";
$db   = "athena";
$user = "root";
$pass = "";
$port = 3307;

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs → exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch propre
            PDO::ATTR_EMULATE_PREPARES   => false                   // sécurité
        ]
    );
} catch (PDOException $e) {
    die("Erreur connexion DB : " . $e->getMessage());
}
