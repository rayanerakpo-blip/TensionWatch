<?php
// Démarrage de la session et vérification que l'utilisateur est connecté
session_start();

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conseils - TensionWatch</title>
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

        <h2 class="mb-4">Conseils pour l'accompagnement</h2>
        <p class="sous-titre mb-4">
            Recommandations générales pour accompagner un proche souffrant de troubles cardiovasculaires.
            Ces conseils ne remplacent pas un avis médical.
        </p>

        <!-- Catégorie : Alimentation -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3 titre-conseil">🥗 Alimentation</h4>
            <ul class="liste-conseil">
                <li>Limiter le sel à moins de 6g par jour (éviter plats industriels et charcuterie).</li>
                <li>Privilégier les fruits, légumes, et aliments riches en potassium (bananes, légumineuses).</li>
                <li>Réduire les graisses saturées et privilégier les huiles végétales (olive, colza).</li>
                <li>Limiter la consommation d'alcool et éviter le grignotage salé.</li>
            </ul>
        </div>

        <!-- Catégorie : Activité physique -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3 titre-conseil">🚶 Activité physique</h4>
            <ul class="liste-conseil">
                <li>Encourager une activité modérée régulière (marche, 30 min/jour si possible).</li>
                <li>Éviter les efforts brusques ou intenses sans avis médical préalable.</li>
                <li>Adapter l'activité selon la tolérance et l'état de fatigue du patient.</li>
            </ul>
        </div>

        <!-- Catégorie : Gestion du stress -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3 titre-conseil">🧘 Gestion du stress</h4>
            <ul class="liste-conseil">
                <li>Le stress chronique peut aggraver l'hypertension : favoriser des moments calmes.</li>
                <li>Encourager des techniques de relaxation (respiration, sophrologie, méditation).</li>
                <li>Veiller à un sommeil suffisant et régulier.</li>
            </ul>
        </div>

        <!-- Catégorie : Suivi du traitement -->
        <div class="card-tw p-4 mb-4">
            <h4 class="mb-3 titre-conseil">💊 Suivi du traitement</h4>
            <ul class="liste-conseil">
                <li>Aider au respect des horaires de prise de médicaments (voir la rubrique "Traitements").</li>
                <li>Ne jamais arrêter un traitement sans avis médical, même en cas d'amélioration.</li>
                <li>Signaler tout effet indésirable au médecin traitant (voir la rubrique "Effets indésirables").</li>
            </ul>
        </div>

        <!-- Catégorie : Quand consulter en urgence -->
        <div class="card-tw p-4 mb-4 card-alerte">
            <h4 class="mb-3 titre-conseil">🚨 Quand consulter en urgence</h4>
            <ul class="liste-conseil">
                <li>Tension artérielle très élevée (supérieure à 180/120 mmHg) accompagnée de symptômes.</li>
                <li>Douleur thoracique, essoufflement soudain, ou troubles de la parole/vision.</li>
                <li>Perte de connaissance, confusion, ou faiblesse brutale d'un côté du corps.</li>
            </ul>
            <p class="mb-0 mt-2">
                <strong>En cas de doute, n'hésitez pas à contacter les secours.</strong>
                Voir la rubrique <a href="urgences.php">Contacts d'urgence</a>.
            </p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>