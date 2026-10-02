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
    header('Location: ../pages/accueil.php');
    exit();
}

// Traitement de l'ajout d'un nouveau médicament
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $dosage = trim($_POST['dosage']);
    $frequence = trim($_POST['frequence']);
    $heure_prise = trim($_POST['heure_prise']);
    $date_debut = trim($_POST['date_debut']);

    if (empty($nom)) {
        $erreur = 'Le nom du médicament est obligatoire.';
    } else {
        // Insertion du nouveau médicament dans la table medicaments
        $stmt = $pdo->prepare("INSERT INTO medicaments (nom, dosage, frequence, heure_prise, date_debut, patient_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $dosage, $frequence, $heure_prise ?: null, $date_debut ?: null, $patient['id']]);

        $succes = 'Médicament ajouté avec succès.';
    }
}

// Traitement de la suppression d'un médicament
if (isset($_GET['supprimer'])) {
    $id_a_supprimer = (int) $_GET['supprimer'];

    // On vérifie que le médicament appartient bien au patient de l'utilisateur connecté (sécurité)
    $stmt = $pdo->prepare("DELETE FROM medicaments WHERE id = ? AND patient_id = ?");
    $stmt->execute([$id_a_supprimer, $patient['id']]);

    header('Location: medicaments.php');
    exit();
}

// Récupération de la liste des médicaments du patient
$stmt = $pdo->prepare("SELECT * FROM medicaments WHERE patient_id = ? ORDER BY date_debut DESC");
$stmt->execute([$patient['id']]);
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Traitements - TensionWatch</title>
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

        <h2 class="mb-4">Traitements du patient</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'un médicament -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Ajouter un médicament</h4>
            <form method="POST" action="">
                <input type="hidden" name="ajouter" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom du médicament</label>
                        <input type="text" class="form-control" name="nom" placeholder="ex: Amlodipine" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Dosage</label>
                        <input type="text" class="form-control" name="dosage" placeholder="ex: 5mg">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fréquence</label>
                        <input type="text" class="form-control" name="frequence" placeholder="ex: 1 fois par jour">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Heure de prise</label>
                        <input type="time" class="form-control" name="heure_prise">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date de début</label>
                        <input type="date" class="form-control" name="date_debut">
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Ajouter le médicament</button>
                </div>
            </form>
        </div>

        <!-- Liste des médicaments enregistrés -->
        <div class="card-tw p-4">
            <h4 class="mb-3">Traitements en cours</h4>
            <?php if (count($medicaments) > 0): ?>
                <table class="table table-borderless mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Dosage</th>
                            <th>Fréquence</th>
                            <th>Heure de prise</th>
                            <th>Date de début</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicaments as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['nom']); ?></td>
                                <td><?php echo htmlspecialchars($m['dosage']); ?></td>
                                <td><?php echo htmlspecialchars($m['frequence']); ?></td>
                                <td>
                                    <?php echo $m['heure_prise'] ? htmlspecialchars(substr($m['heure_prise'], 0, 5)) : '-'; ?>
                                </td>
                                <td>
                                    <?php echo $m['date_debut'] ? htmlspecialchars(date('d/m/Y', strtotime($m['date_debut']))) : '-'; ?>
                                </td>
                                <td>
                                    <a href="?supprimer=<?php echo $m['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer ce médicament ?');">
                                       Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="mb-0">Aucun médicament enregistré pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>