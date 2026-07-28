<?php
// Démarrage de la session pour pouvoir la détruire
session_start();

// Suppression de toutes les variables de session
$_SESSION = [];

// Destruction du cookie de session côté navigateur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruction complète de la session côté serveur
session_destroy();

// Redirection vers la page de connexion
header('Location: login.php');
exit();
?>