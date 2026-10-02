<?php
// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();
require_once '../config/database.php';
require_once '../config/fonctions.php';
// Date et heure actuelles, utilisées pour empêcher la saisie d'une mesure dans le futur
$date_heure_max = date('Y-m-d\TH:i');
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

// Si aucun patient n'est associé, impossible d'enregistrer une mesure
if (!$patient) {
    header('Location: accueil.php');
    exit();
}

// Traitement de l'ajout d'une nouvelle mesure de tension
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $systolique = trim($_POST['systolique']);
    $diastolique = trim($_POST['diastolique']);
    $date_mesure = trim($_POST['date_mesure']);
    $commentaire = trim($_POST['commentaire']);

    //  Vérification des plages physiologiquement possibles pour éviter les valeurs aberrantes
    if (!is_numeric($systolique) || !is_numeric($diastolique) || empty($date_mesure)) {
        $erreur = 'Merci de renseigner des valeurs valides pour la tension et la date.';
    } elseif ($systolique < 40 || $systolique > 300 || $diastolique < 20 || $diastolique > 200) {
        $erreur = 'Les valeurs saisies sont hors des limites physiologiques possibles (systolique : 40-300, diastolique : 20-200).';
    } elseif ($date_mesure > $date_heure_max) {
        $erreur = 'La date de la mesure ne peut pas être dans le futur.';
    } else {
        // Insertion de la nouvelle mesure dans la table tensions
        $stmt = $pdo->prepare("INSERT INTO tensions (systolique, diastolique, date_mesure, commentaire, patient_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$systolique, $diastolique, $date_mesure, $commentaire, $patient['id']]);

        $succes = 'Mesure enregistrée avec succès.';
    }
}

// Récupération de tout l'historique des tensions du patient, du plus ancien au plus récent (pour le graphique)
$stmt = $pdo->prepare("SELECT * FROM tensions WHERE patient_id = ? ORDER BY date_mesure ASC");
$stmt->execute([$patient['id']]);
$historique_tensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparation des données pour Chart.js (labels = dates, data = valeurs)
$labels_graphique = [];
$data_systolique = [];
$data_diastolique = [];

foreach ($historique_tensions as $t) {
    $labels_graphique[] = date('d/m/Y H:i', strtotime($t['date_mesure']));
    $data_systolique[] = $t['systolique'];
    $data_diastolique[] = $t['diastolique'];
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suivi de tension - TensionWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Chart.js pour le graphique d'évolution de la tension -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
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

        <h2 class="mb-4">Suivi de la tension artérielle</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'une nouvelle mesure -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Ajouter une mesure</h4>
            <form method="POST" action="">
                <div class="row g-3">
                   <div class="col-md-3">
                        <label class="form-label">Systolique</label>
                        <input type="number" class="form-control" name="systolique" placeholder="ex: 130" min="40" max="300" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Diastolique</label>
                        <input type="number" class="form-control" name="diastolique" placeholder="ex: 85" min="20" max="200" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date et heure</label>
                        <input type="datetime-local" class="form-control" name="date_mesure" max="<?php echo $date_heure_max; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Commentaire</label>
                        <input type="text" class="form-control" name="commentaire" placeholder="Optionnel">
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-success">Enregistrer la mesure</button>
                </div>
            </form>
        </div>

        <!-- Graphique d'évolution de la tension -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3">Évolution</h4>
            <?php if (count($historique_tensions) > 0): ?>
                <canvas id="graphiqueTension" height="90"></canvas>
            <?php else: ?>
                <p class="mb-0">Aucune donnée à afficher pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Tableau de l'historique complet, du plus récent au plus ancien -->
        <div class="card-tw p-4">
            <h4 class="mb-3">Historique complet</h4>
            <?php if (count($historique_tensions) > 0): ?>
                <table class="table table-borderless mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Systolique</th>
                        <th>Diastolique</th>
                        <th>Statut</th>
                        <th>Commentaire</th>
                    </tr>                    </thead>
                                        <tbody>
                    <?php foreach (array_reverse($historique_tensions) as $t): ?>
                        <?php $eval_ligne = evaluerTension($t['systolique'], $t['diastolique']); ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($t['date_mesure']))); ?></td>
                            <td><?php echo htmlspecialchars($t['systolique']); ?></td>
                            <td><?php echo htmlspecialchars($t['diastolique']); ?></td>
                            <td>
                                <span class="badge-tension badge-tension-<?php echo $eval_ligne['niveau']; ?>">
                                    <?php echo htmlspecialchars($eval_ligne['label']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($t['commentaire']); ?></td>
                        </tr>
                    <?php endforeach; ?>                    
                </tbody>
                </table>
            <?php else: ?>
                <p class="mb-0">Aucune mesure enregistrée pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (count($historique_tensions) > 0): ?>
    <script>
        // Données injectées depuis PHP pour construire le graphique
        const labels = <?php echo json_encode($labels_graphique); ?>;
        const dataSystolique = <?php echo json_encode($data_systolique); ?>;
        const dataDiastolique = <?php echo json_encode($data_diastolique); ?>;

        const ctx = document.getElementById('graphiqueTension').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Systolique',
                        data: dataSystolique,
                        borderColor: '#048b9a',
                        backgroundColor: 'rgba(4, 139, 154, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Diastolique',
                        data: dataDiastolique,
                        borderColor: '#34c759',
                        backgroundColor: 'rgba(52, 199, 89, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>