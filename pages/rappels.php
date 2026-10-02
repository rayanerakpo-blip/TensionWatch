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

// Récupération du patient associé à cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: accueil.php');
    exit();
}

// Traitement de l'ajout d'un nouveau rappel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $date_rappel = trim($_POST['date_rappel']);
    $heure_rappel = trim($_POST['heure_rappel']);

    if (empty($titre) || empty($date_rappel)) {
        $erreur = 'Le titre et la date du rappel sont obligatoires.';
    } else {
        // Insertion du nouveau rappel (les rappels peuvent être définis dans le futur, contrairement aux mesures)
        $stmt = $pdo->prepare("INSERT INTO rappels (titre, description, date_rappel, heure_rappel, patient_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $description ?: null, $date_rappel, $heure_rappel ?: null, $patient['id']]);

        $succes = 'Rappel ajouté avec succès.';
    }
}

// Traitement de la suppression d'un rappel
if (isset($_GET['supprimer'])) {
    $id_a_supprimer = (int) $_GET['supprimer'];

    // Vérification que le rappel appartient bien au patient de l'utilisateur connecté (sécurité)
    $stmt = $pdo->prepare("DELETE FROM rappels WHERE id = ? AND patient_id = ?");
    $stmt->execute([$id_a_supprimer, $patient['id']]);

    header('Location: rappels.php');
    exit();
}

// Récupération de tous les rappels du patient, triés du plus proche au plus lointain
$stmt = $pdo->prepare("SELECT * FROM rappels WHERE patient_id = ? ORDER BY date_rappel ASC, heure_rappel ASC");
$stmt->execute([$patient['id']]);
$rappels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Séparation entre rappels à venir et rappels passés, pour un affichage plus clair
$date_du_jour = date('Y-m-d');
$rappels_a_venir = [];
$rappels_passes = [];

foreach ($rappels as $r) {
    if ($r['date_rappel'] >= $date_du_jour) {
        $rappels_a_venir[] = $r;
    } else {
        $rappels_passes[] = $r;
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rappels - TensionWatch</title>
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

        <h2 class="mb-4">Rappels</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'un rappel -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Ajouter un rappel</h4>
            <form method="POST" action="">
                <input type="hidden" name="ajouter" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" name="titre" placeholder="ex: Prendre l'Amlodipine" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date_rappel" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Heure</label>
                        <input type="time" class="form-control" name="heure_rappel">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Optionnel">
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Ajouter le rappel</button>
                </div>
            </form>
        </div>

        <!-- Rappels à venir -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Rappels à venir</h4>
            <?php if (count($rappels_a_venir) > 0): ?>
                <?php foreach ($rappels_a_venir as $r): ?>
                    <div class="rappel-item p-3 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($r['titre']); ?></strong>
                            <br>
                            <span class="sous-titre">
                                <?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date_rappel']))); ?>
                                <?php if ($r['heure_rappel']): ?>
                                    à <?php echo htmlspecialchars(substr($r['heure_rappel'], 0, 5)); ?>
                                <?php endif; ?>
                            </span>
                            <?php if (!empty($r['description'])): ?>
                                <p class="mb-0 mt-1"><?php echo htmlspecialchars($r['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="?supprimer=<?php echo $r['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer ce rappel ?');">
                           Supprimer
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">Aucun rappel à venir.</p>
            <?php endif; ?>
        </div>

        <!-- Rappels passés -->
        <?php if (count($rappels_passes) > 0): ?>
            <div class="card-tw p-4">
                <h4 class="mb-3">Rappels passés</h4>
                <?php foreach ($rappels_passes as $r): ?>
                    <div class="rappel-item rappel-passe p-3 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($r['titre']); ?></strong>
                            <br>
                            <span class="sous-titre">
                                <?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date_rappel']))); ?>
                                <?php if ($r['heure_rappel']): ?>
                                    à <?php echo htmlspecialchars(substr($r['heure_rappel'], 0, 5)); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <a href="?supprimer=<?php echo $r['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer ce rappel ?');">
                           Supprimer
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>