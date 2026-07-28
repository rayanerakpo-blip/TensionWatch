<?php
// Démarrage de la session pour pouvoir connecter automatiquement l'utilisateur après inscription
session_start();
require_once '../config/database.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des champs du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['mail']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $confirmation = trim($_POST['confirmation']);

    // Vérification que les mots de passe correspondent
    if ($mot_de_passe !== $confirmation) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérification que l'email n'est pas déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà associé à un compte.';
        } else {
            // Hashage sécurisé du mot de passe (jamais en clair dans la base)
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            // Insertion du nouvel utilisateur dans la table utilisateurs
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash]);

            // Récupération de l'id du nouvel utilisateur inséré
            $nouvel_id = $pdo->lastInsertId();

            // Connexion automatique : on crée la session directement
            $_SESSION['utilisateur_id'] = $nouvel_id;
            $_SESSION['utilisateur_nom'] = $nom;
            $_SESSION['utilisateur_prenom'] = $prenom;

            // Redirection vers le tableau de bord
            header('Location: ../pages/Accueil.php');
            exit();
        }
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="login-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-card p-4 rounded-4 shadow">
            <h1 class="text-center mb-1">TensionWatch</h1>
            <p class="sous-titre text-center mb-4">Créer un compte proche aidant</p>

            <?php if ($erreur): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" class="form-control" name="prenom" placeholder="Prénom" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="nom" placeholder="Nom" required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" name="mail" placeholder="Adresse email" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="mot_de_passe" placeholder="Mot de passe" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="confirmation" placeholder="Confirmer le mot de passe" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success">S'inscrire</button>
                </div>
            </form>

            <div class="text-center">
                <a href="login.php" class="d-block">Déjà un compte ? Connectez-vous</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>