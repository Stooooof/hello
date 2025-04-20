<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];
$errors = [];
$success = "";

// Vérification de l'ID du PFE
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: enseignant.php");
    exit();
}

$pfe_id = $_GET['id'];

// Récupération des détails du PFE avec vérification que l'enseignant est bien l'encadrant
$req_pfe = $conn->query("
    SELECT p.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom
    FROM pfes p 
    JOIN etudiants e ON p.etudiant_id = e.id 
    WHERE p.id = $pfe_id AND p.encadrant_in_id = $enseignant_id
");

if ($req_pfe->num_rows === 0) {
    header("Location: enseignant.php");
    exit();
}

$pfe = $req_pfe->fetch_assoc();

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);

    // Validation des données
    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire";
    }

    if (empty($organisme)) {
        $errors[] = "L'organisme est obligatoire";
    }

    if (empty($encadrant_ex)) {
        $errors[] = "L'encadrant externe est obligatoire";
    }

    // Gestion du fichier rapport si fourni
    $rapport = $pfe['rapport']; // Conserver l'ancien rapport par défaut

    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] === 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['rapport']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Le format du rapport n'est pas autorisé. Utilisez PDF, DOC ou DOCX.";
        } else {
            // Chemin pour stocker les fichiers (directement dans htdocs)
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/rapports_pfe/';

            // Création du dossier si nécessaire
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Générer un nom de fichier unique
            $new_filename = uniqid('rapport_') . '.' . $filetype;
            $upload_file = $upload_dir . $new_filename;
            $db_file_path = 'rapports_pfe/' . $new_filename; // Chemin relatif pour la BD

            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $upload_file)) {
                // Supprimer l'ancien rapport si existant
                if (!empty($pfe['rapport']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']);
                }
                $rapport = $db_file_path;
            } else {
                $errors[] = "Erreur lors du téléchargement du fichier.";
            }
        }
    }

    // Si pas d'erreurs, mise à jour du PFE
    if (empty($errors)) {
        $query = "UPDATE pfes SET 
                  titre = ?, 
                  resume = ?, 
                  organisme = ?, 
                  encadrant_ex = ?, 
                  email_ex = ?, 
                  rapport = ? 
                  WHERE id = ? AND encadrant_in_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $rapport, $pfe_id, $enseignant_id);

        if ($stmt->execute()) {
            $success = "Le PFE a été mis à jour avec succès.";
            // Mettre à jour les données affichées
            $pfe['titre'] = $titre;
            $pfe['resume'] = $resume;
            $pfe['organisme'] = $organisme;
            $pfe['encadrant_ex'] = $encadrant_ex;
            $pfe['email_ex'] = $email_ex;
            $pfe['rapport'] = $rapport;
        } else {
            $errors[] = "Erreur lors de la mise à jour: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier PFE - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body>
<?php include('enseignant_header.php'); ?>

<div class="container">
    <h1>Modifier le PFE</h1>

    <div class="back-link">
        <a href="consulter_pfe.php?id=<?= $pfe_id ?>">← Retour aux détails</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-message">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h2>Projet de: <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?></h2>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre du PFE:</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($pfe['titre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="resume">Résumé:</label>
                <textarea id="resume" name="resume" rows="8"><?= htmlspecialchars($pfe['resume']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="organisme">Organisme:</label>
                <input type="text" id="organisme" name="organisme" value="<?= htmlspecialchars($pfe['organisme']) ?>" required>
            </div>

            <div class="form-group">
                <label for="encadrant_ex">Encadrant externe:</label>
                <input type="text" id="encadrant_ex" name="encadrant_ex" value="<?= htmlspecialchars($pfe['encadrant_ex']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email_ex">Email encadrant externe:</label>
                <input type="email" id="email_ex" name="email_ex" value="<?= htmlspecialchars($pfe['email_ex']) ?>">
            </div>

            <div class="form-group">
                <label for="rapport">Rapport (PDF, DOC, DOCX):</label>
                <?php if (!empty($pfe['rapport'])): ?>
                    <p class="file-info">
                        Rapport actuel:
                        <a href="/<?= htmlspecialchars($pfe['rapport']) ?>" target="_blank">
                            <?= htmlspecialchars(basename($pfe['rapport'])) ?>
                        </a>
                        <span>(Taille: <?= round(filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']) / 1024) ?> KB)</span>
                    </p>
                <?php else: ?>
                    <p class="file-info">Aucun rapport actuellement.</p>
                <?php endif; ?>
                <input type="file" id="rapport" name="rapport">
                <p class="help-text">Laissez vide pour conserver le rapport actuel.</p>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<?php include('enseignant_footer.php'); ?>
</body>
</html>