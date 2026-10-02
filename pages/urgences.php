<?php
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

// Récupération du patient associé à cet utilisateur, pour afficher son médecin traitant
$stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: accueil.php');
    exit();
}

// Traitement de l'ajout d'un contact d'urgence personnalisé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $lien_avec_patient = trim($_POST['lien_avec_patient']);

    if (empty($nom) || empty($telephone)) {
        $erreur = 'Le nom et le téléphone du contact sont obligatoires.';
    } else {
        // Insertion du nouveau contact dans la table contacts_urgence
        $stmt = $pdo->prepare("INSERT INTO contacts_urgence (nom, telephone, lien_avec_patient, patient_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $telephone, $lien_avec_patient ?: null, $patient['id']]);

        $succes = 'Contact d\'urgence ajouté avec succès.';
    }
}

// Traitement de la suppression d'un contact d'urgence
if (isset($_GET['supprimer'])) {
    $id_a_supprimer = (int) $_GET['supprimer'];

    // On vérifie que le contact appartient bien au patient de l'utilisateur connecté (sécurité)
    $stmt = $pdo->prepare("DELETE FROM contacts_urgence WHERE id = ? AND patient_id = ?");
    $stmt->execute([$id_a_supprimer, $patient['id']]);

    header('Location: urgences.php');
    exit();
}

// Récupération des contacts d'urgence personnalisés du patient
$stmt = $pdo->prepare("SELECT * FROM contacts_urgence WHERE patient_id = ? ORDER BY id ASC");
$stmt->execute([$patient['id']]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacts d'urgence - TensionWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Barre de navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-tw">
        <div class="container-fluid">
            <a href="accueil.php" class="navbar-brand text-white text-decoration-none">← TensionWatch</a>
            <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </nav>

    <div class="container py-4">

        <h2 class="mb-4">Contacts d'urgence</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Numéros d'urgence nationaux -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Numéros d'urgence</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="tel:15" class="contact-urgence d-block text-center p-3">
                        <span class="numero-urgence">15</span>
                        <span class="label-urgence">SAMU</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="tel:18" class="contact-urgence d-block text-center p-3">
                        <span class="numero-urgence">18</span>
                        <span class="label-urgence">Pompiers</span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="tel:112" class="contact-urgence d-block text-center p-3">
                        <span class="numero-urgence">112</span>
                        <span class="label-urgence">Numéro d'urgence européen</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Médecin traitant du patient -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Médecin traitant</h4>
            <?php if (!empty($patient['medecin'])): ?>
                <p class="mb-0">
                    <strong><?php echo htmlspecialchars($patient['medecin']); ?></strong> —
                    médecin traitant de <?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?>.
                </p>
            <?php else: ?>
                <p class="mb-0">Aucun médecin traitant renseigné pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Formulaire d'ajout d'un contact d'urgence personnalisé -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Ajouter un contact personnalisé</h4>
            <form method="POST" action="">
                <input type="hidden" name="ajouter" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" placeholder="ex: Marie Dupont" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="telephone" placeholder="ex: 0612345678" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lien avec le patient</label>
                        <input type="text" class="form-control" name="lien_avec_patient" placeholder="ex: Fille, Voisin...">
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Ajouter le contact</button>
                </div>
            </form>
        </div>

        <!-- Liste des contacts d'urgence personnalisés -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Mes contacts personnalisés</h4>
            <?php if (count($contacts) > 0): ?>
                <div class="row g-3">
                    <?php foreach ($contacts as $c): ?>
                        <div class="col-md-6">
                            <div class="contact-perso p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($c['nom']); ?></strong>
                                    <?php if (!empty($c['lien_avec_patient'])): ?>
                                        <span class="sous-titre"> — <?php echo htmlspecialchars($c['lien_avec_patient']); ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <a href="tel:<?php echo htmlspecialchars($c['telephone']); ?>" class="lien-telephone">
                                        <?php echo htmlspecialchars($c['telephone']); ?>
                                    </a>
                                </div>
                                <a href="?supprimer=<?php echo $c['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer ce contact ?');">
                                   Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="mb-0">Aucun contact personnalisé ajouté pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Antécédents médicaux (utile à rappeler aux secours) -->
        <?php if (!empty($patient['antecedants'])): ?>
        <div class="card-tw p-4 card-alerte">
            <h4 class="mb-3">Antécédents médicaux à signaler aux secours</h4>
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($patient['antecedants'])); ?></p>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>