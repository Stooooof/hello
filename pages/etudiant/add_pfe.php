<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}

// Récupérer l'ID de l'étudiant
$email = $_SESSION['email'];
$etudiant_query = $conn->query("SELECT id FROM etudiants WHERE email = '$email'");
if ($etudiant_query->num_rows === 0) {
    die("Vous devez avoir un profil étudiant complet pour ajouter un PFE");
}
$etudiant = $etudiant_query->fetch_assoc();
$etudiant_id = $etudiant['id'];

$errors = [];
$uploadDir = "C:/xampp/htdocs/pfe_uploads/";

// Créer le dossier si inexistant
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);
    $encadrant_in_id = (int)$_POST['encadrant_in_id'];

    // Validation
    if (empty($titre)) $errors['titre'] = "Titre obligatoire";
    if (empty($organisme)) $errors['organisme'] = "Organisme obligatoire";
    if (empty($encadrant_ex)) $errors['encadrant_ex'] = "Encadrant externe obligatoire";
    if (empty($encadrant_in_id)) $errors['encadrant_in'] = "Encadrant interne obligatoire";

    // Gestion du fichier
    $rapportPath = null;
    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] === 0) {
        $fileExt = strtolower(pathinfo($_FILES['rapport']['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'pdf') {
            $errors['rapport'] = "Seuls les PDF sont acceptés";
        } elseif ($_FILES['rapport']['size'] > 5000000) {
            $errors['rapport'] = "Fichier trop volumineux (max 5MB)";
        } else {
            $fileName = uniqid().'.pdf';
            $targetPath = $uploadDir.$fileName;
            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $targetPath)) {
                $rapportPath = "pfe_uploads/".$fileName;
            } else {
                $errors['rapport'] = "Erreur lors de l'upload";
            }
        }
    } else {
        $errors['rapport'] = "Fichier requis";
    }

    // Insertion si pas d'erreurs
    if (empty($errors)) {
        $sql = "INSERT INTO pfes (titre, resume, organisme, encadrant_ex, email_ex, encadrant_in_id, etudiant_id, rapport)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiis", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $encadrant_in_id, $etudiant_id, $rapportPath);

        if ($stmt->execute()) {
            $_SESSION['success'] = "PFE ajouté avec succès";
            header('Location: etudiant.php');
            exit();
        } else {
            $errors['db'] = "Erreur: ".$conn->error;
            if ($rapportPath && file_exists($uploadDir.basename($rapportPath))) {
                unlink($uploadDir.basename($rapportPath));
            }
        }
    }
}

// Récupérer les enseignants pour le select
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
    <style>
        .error { color: red; font-size: 0.9em; }
        textarea { width: 100%; min-height: 100px; }
    </style>
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form method="post" enctype="multipart/form-data">
            <h2 class="login_h2">Ajouter un PFE</h2>

            <?php if (!empty($errors['db'])): ?>
                <p class="error"><?= $errors['db'] ?></p>
            <?php endif; ?>

            <input class="login_input" type="text" name="titre" placeholder="Titre*" required>
            <?php if (!empty($errors['titre'])): ?>
                <span class="error"><?= $errors['titre'] ?></span>
            <?php endif; ?>

            <textarea class="login_input" name="resume" placeholder="Résumé"></textarea>

            <input class="login_input" type="text" name="organisme" placeholder="Organisme*" required>
            <?php if (!empty($errors['organisme'])): ?>
                <span class="error"><?= $errors['organisme'] ?></span>
            <?php endif; ?>

            <input class="login_input" type="text" name="encadrant_ex" placeholder="Encadrant externe*" required>
            <?php if (!empty($errors['encadrant_ex'])): ?>
                <span class="error"><?= $errors['encadrant_ex'] ?></span>
            <?php endif; ?>

            <input class="login_input" type="email" name="email_ex" placeholder="Email encadrant externe">

            <select class="login_select" name="encadrant_in_id" required>
                <option value="">-- Encadrant interne --</option>
                <?php while ($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>"><?= $ens['prenom'].' '.$ens['nom'] ?></option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['encadrant_in'])): ?>
                <span class="error"><?= $errors['encadrant_in'] ?></span>
            <?php endif; ?>

            <label>Rapport (PDF)*</label>
            <input class="login_input" type="file" name="rapport" accept=".pdf" required>
            <?php if (!empty($errors['rapport'])): ?>
                <span class="error"><?= $errors['rapport'] ?></span>
            <?php endif; ?>

            <button class="login_button" type="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
