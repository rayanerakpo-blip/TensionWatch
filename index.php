<?php
// Point d'entrée du site : redirige selon que l'utilisateur est connecté ou non
session_start();

if (isset($_SESSION['utilisateur_id'])) {
    // Utilisateur déjà connecté -> tableau de bord
    header('Location: pages/accueil.php');
} else {
    // Utilisateur non connecté -> page de login
    header('Location: auth/login.php');
}
exit();
?>