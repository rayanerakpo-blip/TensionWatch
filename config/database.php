<?php
// Identifiants de connexion à la base de données hébergée sur InfinityFree
$host = '....';
$base = '...';
$user = '...';
$pass = 'TON_MOT_DE_PASSE_ICI'; // remplace par ton vrai mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$base", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
