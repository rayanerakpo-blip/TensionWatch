<?php
session_start();
require_once '../config/database.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['mail']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        $_SESSION['utilisateur_id'] = $utilisateur['id'];
        $_SESSION['utilisateur_nom'] = $utilisateur['nom'];
        $_SESSION['utilisateur_prenom'] = $utilisateur['prenom'];

        header('Location: ../pages/accueil.php');
        exit();
    } else {
        $erreur = 'Email ou mot de passe incorrect.';
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="login-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-card p-4 rounded-4 shadow">
            <h1 class="text-center mb-1">TensionWatch</h1>
            <p class="sous-titre text-center mb-4">Suivi médical pour proches aidants</p>

            <?php if ($erreur): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" class="form-control" name="mail" placeholder="Adresse email" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="mot_de_passe" placeholder="Mot de passe" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success">Se connecter</button>
                </div>
            </form>

            <div class="text-center">
                <a href="register.php" class="d-block mb-1">Pas encore de compte ? Inscrivez-vous </a>
                <a href="#" class="d-block">Mot de passe oublié ?</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
