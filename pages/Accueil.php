<?php
// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();
require_once '../config/database.php';
require_once '../config/fonctions.php';

// Si l'utilisateur n'est pas connecté, on le renvoie vers la page de login
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupération du patient associé à cet utilisateur (le proche aidant)
$stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Variables par défaut si aucun patient n'est encore enregistré
$derniere_tension = null;
$historique_tensions = [];
$evaluation = null;

if ($patient) {
    // Récupération de la dernière mesure de tension du patient
    $stmt = $pdo->prepare("SELECT * FROM tensions WHERE patient_id = ? ORDER BY date_mesure DESC LIMIT 1");
    $stmt->execute([$patient['id']]);
    $derniere_tension = $stmt->fetch(PDO::FETCH_ASSOC);

    // Évaluation du niveau de gravité de la dernière mesure, si elle existe
    if ($derniere_tension) {
        $evaluation = evaluerTension($derniere_tension['systolique'], $derniere_tension['diastolique']);
    }

    // Récupération des 5 dernières mesures pour un mini historique
    $stmt = $pdo->prepare("SELECT * FROM tensions WHERE patient_id = ? ORDER BY date_mesure DESC LIMIT 5");
    $stmt->execute([$patient['id']]);
    $historique_tensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord - TensionWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <!-- Barre de navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-tw">
        <div class="container-fluid">
            <span class="navbar-brand">TensionWatch</span>
            <div class="d-flex align-items-center">
                <a href="compte.php" class="me-3 text-white text-decoration-none">
                    Bonjour, <?php echo htmlspecialchars($_SESSION['utilisateur_prenom']); ?>
                </a>
                <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>            </div>
        </div>
    </nav>

    <div class="container py-4">

        <?php if (!$patient): ?>
            <!-- Aucun patient enregistré pour cet utilisateur -->
            <div class="alert alert-warning text-center">
                Aucun patient n'est encore associé à votre compte.
                <a href="patient.php" class="alert-link">Cliquez ici pour ajouter un patient</a>.
            </div>
        <?php else: ?>

            <!-- Alerte visible si la dernière tension est anormale -->
            <?php if ($evaluation && $evaluation['niveau'] !== 'normale'): ?>
                <div class="alerte-tension alerte-tension-<?php echo $evaluation['niveau']; ?> mb-4 p-3 text-center">
                    ⚠️ <?php echo htmlspecialchars($evaluation['label']); ?>
                </div>
            <?php endif; ?>

            <!-- Carte d'information sur le patient suivi -->
            <div class="card-tw p-4 mb-4 d-flex flex-row justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?></h2>
                    <p class="sous-titre mb-0">
                        Né(e) le <?php echo htmlspecialchars(date('d/m/Y', strtotime($patient['Date_de_naissance']))); ?>
                        &nbsp;|&nbsp; Groupe sanguin : <?php echo htmlspecialchars($patient['groupe_sanguin']); ?>
                        &nbsp;|&nbsp; Poids : <?php echo htmlspecialchars($patient['poids']); ?> kg
                    </p>
                </div>
                <a href="patient.php" class="btn btn-sm btn-outline-light text-nowrap" style="border-color:#048b9a;color:#048b9a;">Modifier</a>
            </div>

            <!-- Carte de la dernière mesure de tension -->
            <div class="card-tw p-4 mb-4">
                <h4 class="mb-3">Dernière mesure de tension</h4>
                <?php if ($derniere_tension): ?>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="valeur-tension">
                                <?php echo htmlspecialchars($derniere_tension['systolique']); ?>/<?php echo htmlspecialchars($derniere_tension['diastolique']); ?>
                            </span>
                            <span class="unite-tension">mmHg</span>
                            <br>
                            <span class="badge-tension badge-tension-<?php echo $evaluation['niveau']; ?> mt-2 d-inline-block">
                                <?php echo htmlspecialchars($evaluation['label']); ?>
                            </span>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 sous-titre">
                                <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($derniere_tension['date_mesure']))); ?>
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($derniere_tension['commentaire'])): ?>
                        <p class="mt-2 mb-0"><?php echo htmlspecialchars($derniere_tension['commentaire']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="mb-0">Aucune mesure enregistrée pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Mini historique des 5 dernières mesures -->
            <?php if (count($historique_tensions) > 1): ?>
                <div class="card-tw p-4 mb-4">
                    <h4 class="mb-3">Historique récent</h4>
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Systolique</th>
                                <th>Diastolique</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique_tensions as $t): ?>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php endif; ?>

<!-- Menu de navigation vers les autres fonctionnalités -->
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="tension.php" class="btn-menu w-100 d-block text-center p-3">Tension</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="medicaments.php" class="btn-menu w-100 d-block text-center p-3">Traitements</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="effets.php" class="btn-menu w-100 d-block text-center p-3">Effets indésirables</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="conseils.php" class="btn-menu w-100 d-block text-center p-3">Conseils</a>
            </div>
            <div class="col-6 col-md-6">
                <a href="urgences.php" class="btn-menu-urgence w-100 d-block text-center p-3">Contacts d'urgence</a>
            </div>
            <div class="col-6 col-md-6">
                <a href="export_pdf.php" class="btn-menu w-100 d-block text-center p-3">📄 Exporter le dossier</a>
            </div>
            <div class="col-12">
                <a href="rappels.php" class="btn-menu w-100 d-block text-center p-3">🔔 Rappels</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>