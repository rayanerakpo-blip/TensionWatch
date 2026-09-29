<?php
// MODÈLE de configuration : copier ce fichier en config/database.php puis renseigner vos identifiants.
// config/database.php est ignoré par Git et ne sera jamais écrasé par le déploiement automatique.
$host = 'localhost';      // Hôte MySQL (en production : celui fourni par InfinityFree)
$base = 'tensionwatch';   // Nom de la base
$user = 'root';           // Utilisateur MySQL
$pass = '';               // Mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$base;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // On journalise le détail technique, mais on n'affiche rien de sensible au visiteur
    error_log('Connexion BDD impossible : ' . $e->getMessage());
    die('Erreur de connexion à la base de données.');
}