<?php


// Lignes temporaires pour le débogage - À RETIRER une fois le problème résolu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();
require_once '../config/database.php';



if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$erreur = '';
$succes = '';

// Récupération des informations actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$utilisateur_id]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement de la mise à jour des informations personnelles (nom, prénom, email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_infos'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    if (empty($nom) || empty($prenom) || empty($email)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        // Vérification que l'email n'est pas déjà utilisé par un autre compte
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$email, $utilisateur_id]);

        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé par un autre compte.';
        } else {
            // Mise à jour des informations dans la table utilisateurs
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $utilisateur_id]);

            // Mise à jour des variables de session pour refléter les changements immédiatement
            $_SESSION['utilisateur_nom'] = $nom;
            $_SESSION['utilisateur_prenom'] = $prenom;

            $succes = 'Informations mises à jour avec succès.';

            // Rechargement des données à jour
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$utilisateur_id]);
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_mdp'])) {
    $ancien_mdp = trim($_POST['ancien_mdp']);
    $nouveau_mdp = trim($_POST['nouveau_mdp']);
    $confirmation_mdp = trim($_POST['confirmation_mdp']);

    // Vérification que l'ancien mot de passe est correct
    if (!password_verify($ancien_mdp, $utilisateur['mot_de_passe'])) {
        $erreur = 'Le mot de passe actuel est incorrect.';
    } elseif ($nouveau_mdp !== $confirmation_mdp) {
        $erreur = 'Les nouveaux mots de passe ne correspondent pas.';
    } elseif (strlen($nouveau_mdp) < 6) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Hashage et enregistrement du nouveau mot de passe
        $nouveau_mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$nouveau_mdp_hash, $utilisateur_id]);

        $succes = 'Mot de passe modifié avec succès.';
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon compte - TensionWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Barre de navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-tw">
        <div class="container-fluid">
            <a href="Accueil.php" class="navbar-brand text-white text-decoration-none">← TensionWatch</a>
            <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </nav>

    <div class="container py-4">

        <h2 class="mb-4">Mon compte</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire de modification des informations personnelles -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Informations personnelles</h4>
            <form method="POST" action="">
                <input type="hidden" name="modifier_infos" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom"
                               value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom"
                               value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Adresse email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
                </div>
            </form>
        </div>

        <!-- Formulaire de changement de mot de passe -->
        <div class="card-tw p-4">
            <h4 class="mb-3">Changer le mot de passe</h4>
            <form method="POST" action="">
                <input type="hidden" name="modifier_mdp" value="1">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="ancien_mdp" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="nouveau_mdp" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" name="confirmation_mdp" required>
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Changer le mot de passe</button>
                </div>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>