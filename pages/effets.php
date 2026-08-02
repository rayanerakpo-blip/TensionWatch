<?php
// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();
require_once '../config/database.php';
// Date actuelle, utilisée pour empêcher la déclaration d'un effet dans le futur
$date_max = date('Y-m-d');
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
    header('Location: Accueil.php');
    exit();
}

// Traitement de l'ajout d'un nouvel effet indésirable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $type_effet = trim($_POST['type_effet']);
    $intensite = trim($_POST['intensite']);
    $frequence = trim($_POST['frequence']);
    $description = trim($_POST['description']);
    $date_effet = trim($_POST['date_effet']);

    // Vérification basique des champs obligatoires
    if (empty($type_effet) || !is_numeric($intensite) || empty($date_effet)) {
        $erreur = 'Merci de renseigner au minimum le type d\'effet, l\'intensité et la date.';
    } elseif ($date_effet > $date_max) {
        $erreur = 'La date de l\'effet indésirable ne peut pas être dans le futur.';
    } else {
        // Insertion du nouvel effet indésirable dans la table effets_indesirables
        $stmt = $pdo->prepare("INSERT INTO effets_indesirables (type_effet, intensite, frequence, description, date_effet, patient_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type_effet, $intensite, $frequence, $description, $date_effet, $patient['id']]);

        $succes = 'Effet indésirable signalé avec succès.';
    }
}

// Traitement de la suppression d'un effet indésirable
if (isset($_GET['supprimer'])) {
    $id_a_supprimer = (int) $_GET['supprimer'];

    // On vérifie que l'effet appartient bien au patient de l'utilisateur connecté (sécurité)
    $stmt = $pdo->prepare("DELETE FROM effets_indesirables WHERE id = ? AND patient_id = ?");
    $stmt->execute([$id_a_supprimer, $patient['id']]);

    header('Location: effets.php');
    exit();
}

// Récupération de l'historique des effets indésirables du patient, du plus récent au plus ancien
$stmt = $pdo->prepare("SELECT * FROM effets_indesirables WHERE patient_id = ? ORDER BY date_effet DESC");
$stmt->execute([$patient['id']]);
$effets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Effets indésirables - TensionWatch</title>
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

        <h2 class="mb-4">Effets indésirables</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire de signalement d'un effet indésirable -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Signaler un effet indésirable</h4>
            <form method="POST" action="">
                <input type="hidden" name="ajouter" value="1">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type d'effet</label>
                        <input type="text" class="form-control" name="type_effet" placeholder="ex: Vertiges" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Intensité (1-10)</label>
                        <input type="number" class="form-control" name="intensite" min="1" max="10" placeholder="ex: 5" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fréquence</label>
                        <input type="text" class="form-control" name="frequence" placeholder="ex: Occasionnelle">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date_effet" max="<?php echo $date_max; ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Optionnel">
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Signaler l'effet</button>
                </div>
            </form>
        </div>

        <!-- Historique des effets indésirables signalés -->
        <div class="card-tw p-4">
            <h4 class="mb-3">Historique des signalements</h4>
            <?php if (count($effets) > 0): ?>
                <table class="table table-borderless mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type d'effet</th>
                            <th>Intensité</th>
                            <th>Fréquence</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($effets as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($e['date_effet']))); ?></td>
                                <td><?php echo htmlspecialchars($e['type_effet']); ?></td>
                                <td>
                                    <span class="badge-intensite badge-intensite-<?php echo (int) $e['intensite'] >= 7 ? 'haute' : ((int) $e['intensite'] >= 4 ? 'moyenne' : 'basse'); ?>">
                                        <?php echo htmlspecialchars($e['intensite']); ?>/10
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($e['frequence']); ?></td>
                                <td><?php echo htmlspecialchars($e['description']); ?></td>
                                <td>
                                    <a href="?supprimer=<?php echo $e['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer ce signalement ?');">
                                       Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="mb-0">Aucun effet indésirable signalé pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>