<?php
// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();
require_once '../config/database.php';
require_once '../config/fonctions.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupération du patient associé à cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: Accueil.php');
    exit();
}

// Récupération de tout l'historique des tensions, du plus récent au plus ancien
$stmt = $pdo->prepare("SELECT * FROM tensions WHERE patient_id = ? ORDER BY date_mesure DESC");
$stmt->execute([$patient['id']]);
$historique_tensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des traitements en cours
$stmt = $pdo->prepare("SELECT * FROM medicaments WHERE patient_id = ? ORDER BY date_debut DESC");
$stmt->execute([$patient['id']]);
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des effets indésirables signalés
$stmt = $pdo->prepare("SELECT * FROM effets_indesirables WHERE patient_id = ? ORDER BY date_effet DESC");
$stmt->execute([$patient['id']]);
$effets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Export - Dossier patient - TensionWatch</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Styles pour l'affichage à l'écran */
        * {
            font-family: 'Comfortaa', cursive;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333333;
        }

        .barre-actions {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-imprimer {
            background-color: #34c759;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-retour {
            background-color: #ffffff;
            color: #048b9a;
            border: 2px solid #048b9a;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
        }

        .document {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .entete-document {
            border-bottom: 3px solid #048b9a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .entete-document h1 {
            color: #048b9a;
            margin: 0 0 4px 0;
            font-size: 24px;
        }

        .entete-document p {
            margin: 0;
            font-size: 12px;
            color: #888888;
        }

        .section-document {
            margin-bottom: 28px;
        }

        .section-document h2 {
            color: #048b9a;
            font-size: 16px;
            border-bottom: 1px solid #dddddd;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background-color: #048b9a;
            color: #ffffff;
            padding: 8px;
            text-align: left;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #eeeeee;
        }

        .info-patient {
            font-size: 13px;
            line-height: 1.8;
        }

        .pied-page {
            margin-top: 30px;
            font-size: 11px;
            color: #999999;
            text-align: center;
        }

        /* Styles spécifiques à l'impression : masque les boutons et nettoie la mise en page */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .barre-actions {
                display: none;
            }

            .document {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Barre d'actions (masquée à l'impression) -->
    <div class="barre-actions">
        <a href="Accueil.php" class="btn-retour">← Retour au tableau de bord</a>
        <button class="btn-imprimer" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
    </div>

    <!-- Document exportable -->
    <div class="document">

        <div class="entete-document">
            <h1>TensionWatch — Dossier patient</h1>
            <p>Document généré le <?php echo date('d/m/Y à H:i'); ?></p>
        </div>

        <!-- Informations générales du patient -->
        <div class="section-document">
            <h2>Informations patient</h2>
            <div class="info-patient">
                <strong><?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?></strong><br>
                Né(e) le <?php echo htmlspecialchars(date('d/m/Y', strtotime($patient['Date_de_naissance']))); ?><br>
                Groupe sanguin : <?php echo htmlspecialchars($patient['groupe_sanguin']); ?><br>
                Poids : <?php echo htmlspecialchars($patient['poids']); ?> kg<br>
                Médecin traitant : <?php echo htmlspecialchars($patient['medecin'] ?: 'Non renseigné'); ?><br>
                <?php if (!empty($patient['antecedants'])): ?>
                    Antécédents : <?php echo nl2br(htmlspecialchars($patient['antecedants'])); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historique des mesures de tension -->
        <div class="section-document">
            <h2>Historique des mesures de tension</h2>
            <?php if (count($historique_tensions) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Systolique</th>
                            <th>Diastolique</th>
                            <th>Statut</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique_tensions as $t): ?>
                            <?php $eval = evaluerTension($t['systolique'], $t['diastolique']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($t['date_mesure']))); ?></td>
                                <td><?php echo htmlspecialchars($t['systolique']); ?></td>
                                <td><?php echo htmlspecialchars($t['diastolique']); ?></td>
                                <td><?php echo htmlspecialchars($eval['label']); ?></td>
                                <td><?php echo htmlspecialchars($t['commentaire']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune mesure enregistrée.</p>
            <?php endif; ?>
        </div>

        <!-- Traitements en cours -->
        <div class="section-document">
            <h2>Traitements</h2>
            <?php if (count($medicaments) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Dosage</th>
                            <th>Fréquence</th>
                            <th>Heure de prise</th>
                            <th>Date de début</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicaments as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['nom']); ?></td>
                                <td><?php echo htmlspecialchars($m['dosage']); ?></td>
                                <td><?php echo htmlspecialchars($m['frequence']); ?></td>
                                <td><?php echo $m['heure_prise'] ? htmlspecialchars(substr($m['heure_prise'], 0, 5)) : '-'; ?></td>
                                <td><?php echo $m['date_debut'] ? htmlspecialchars(date('d/m/Y', strtotime($m['date_debut']))) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucun traitement enregistré.</p>
            <?php endif; ?>
        </div>

        <!-- Effets indésirables signalés -->
        <div class="section-document">
            <h2>Effets indésirables signalés</h2>
            <?php if (count($effets) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type d'effet</th>
                            <th>Intensité</th>
                            <th>Fréquence</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($effets as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($e['date_effet']))); ?></td>
                                <td><?php echo htmlspecialchars($e['type_effet']); ?></td>
                                <td><?php echo htmlspecialchars($e['intensite']); ?>/10</td>
                                <td><?php echo htmlspecialchars($e['frequence']); ?></td>
                                <td><?php echo htmlspecialchars($e['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucun effet indésirable signalé.</p>
            <?php endif; ?>
        </div>

        <div class="pied-page">
            Document généré automatiquement par TensionWatch — à destination du personnel médical.
        </div>

    </div>

</body>
</html>