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

// Récupération du patient existant pour cet utilisateur (s'il y en a un)
$stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire (création ou modification du patient)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_de_naissance = trim($_POST['date_de_naissance']);
    $groupe_sanguin = trim($_POST['groupe_sanguin']);
    $poids = trim($_POST['poids']);
    $medecin = trim($_POST['medecin']);
    $antecedants = trim($_POST['antecedants']);

    // Vérification des champs obligatoires
    if (empty($nom) || empty($prenom)) {
        $erreur = 'Le nom et le prénom du patient sont obligatoires.';
    } else {
        if ($patient) {
            // Un patient existe déjà pour cet utilisateur -> mise à jour (UPDATE)
            $stmt = $pdo->prepare("UPDATE patients SET nom = ?, prenom = ?, Date_de_naissance = ?, groupe_sanguin = ?, poids = ?, medecin = ?, antecedants = ? WHERE id = ? AND utilisateur_id = ?");
            $stmt->execute([
                $nom, $prenom, $date_de_naissance ?: null, $groupe_sanguin ?: null,
                $poids ?: null, $medecin ?: null, $antecedants ?: null,
                $patient['id'], $utilisateur_id
            ]);

            $succes = 'Profil patient mis à jour avec succès.';
        } else {
            // Aucun patient existant -> création (INSERT)
            $stmt = $pdo->prepare("INSERT INTO patients (nom, prenom, Date_de_naissance, groupe_sanguin, poids, medecin, antecedants, utilisateur_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nom, $prenom, $date_de_naissance ?: null, $groupe_sanguin ?: null,
                $poids ?: null, $medecin ?: null, $antecedants ?: null,
                $utilisateur_id
            ]);

            $succes = 'Patient créé avec succès.';
        }

        // On recharge les données du patient à jour pour réafficher le formulaire pré-rempli
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE utilisateur_id = ?");
        $stmt->execute([$utilisateur_id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil patient - TensionWatch</title>
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

        <h2 class="mb-4"><?php echo $patient ? 'Modifier le profil patient' : 'Ajouter un patient'; ?></h2>

        <?php if ($erreur): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($succes); ?></div>
        <?php endif; ?>

        <!-- Formulaire de création/modification du patient -->
        <div class="card-tw p-4">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom"
                               value="<?php echo $patient ? htmlspecialchars($patient['prenom']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom"
                               value="<?php echo $patient ? htmlspecialchars($patient['nom']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" class="form-control" name="date_de_naissance"
                               value="<?php echo $patient ? htmlspecialchars($patient['Date_de_naissance']) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Groupe sanguin</label>
                        <select class="form-control" name="groupe_sanguin">
                            <option value="">-- Sélectionner --</option>
                            <?php
                            // Liste des groupes sanguins possibles
                            $groupes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            foreach ($groupes as $g) {
                                $selectionne = ($patient && $patient['groupe_sanguin'] === $g) ? 'selected' : '';
                                echo "<option value=\"$g\" $selectionne>$g</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" step="0.1" class="form-control" name="poids"
                               value="<?php echo $patient ? htmlspecialchars($patient['poids']) : ''; ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Médecin traitant</label>
                        <input type="text" class="form-control" name="medecin" placeholder="Nom du médecin"
                               value="<?php echo $patient ? htmlspecialchars($patient['medecin']) : ''; ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Antécédents médicaux</label>
                        <textarea class="form-control" name="antecedants" rows="4"
                                  placeholder="ex: Hypertension depuis 2018, diabète type 2..."><?php echo $patient ? htmlspecialchars($patient['antecedants']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success">
                        <?php echo $patient ? 'Enregistrer les modifications' : 'Créer le patient'; ?>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>