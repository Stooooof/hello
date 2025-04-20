<?php
session_start();
include('../../connexion_db.php');

// Vérification de session
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

// Initialisation
$errors = [];
$uploadDir = "C:/xampp/htdocs/pfe_uploads/";

// Créer le dossier s'il n'existe pas
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Récupérer le rôle de l'utilisateur
$email = $_SESSION['email'];
$user_query = $conn->query("SELECT role FROM users WHERE email = '$email'");
$user = $user_query->fetch_assoc();
$role = $user['role'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);
    $encadrant_in_id = (int)$_POST['encadrant_in_id'];

    // Gestion de l'étudiant selon le rôle
    if ($role === 'etudiant') {
        // Si étudiant, on prend son propre ID
        $etudiant_query = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
        $etudiant = $etudiant_query->fetch_assoc();
        $etudiant_id = $etudiant['user_id'];
    } elseif ($role === 'admin' && isset($_POST['etudiant_id'])) {
        // Si admin, on prend l'étudiant sélectionné
        $etudiant_id = (int)$_POST['etudiant_id'];
    } else {
        $errors['etudiant'] = "Sélection d'étudiant requise";
    }

    // Validation
    if (empty($titre)) $errors['titre'] = "Titre obligatoire";
    if (empty($organisme)) $errors['organisme'] = "Organisme obligatoire";
    if (empty($encadrant_ex)) $errors['encadrant_ex'] = "Encadrant externe obligatoire";

    // Gestion du fichier
    $rapportPath = null;
    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] == 0) {
        $fileExt = strtolower(pathinfo($_FILES['rapport']['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'pdf') {
            $errors['rapport'] = "Seuls les PDF sont acceptés";
        } else {
            $fileName = uniqid() . '.pdf';
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $targetPath)) {
                $rapportPath = "pfe_uploads/" . $fileName; // Chemin relatif pour la BD
            } else {
                $errors['rapport'] = "Erreur lors de l'upload";
            }
        }
    }

    // Insertion si pas d'erreurs
    if (empty($errors) && isset($etudiant_id)) {
        $sql = "INSERT INTO pfes 
                (titre, resume, organisme, encadrant_ex, email_ex, encadrant_in_id, etudiant_id, rapport)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiis", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $encadrant_in_id, $etudiant_id, $rapportPath);

        if ($stmt->execute()) {
            $_SESSION['success'] = "PFE ajouté avec succès";
            header('Location: table_pfes.php');
            exit();
        } else {
            $errors['db'] = "Erreur: " . $conn->error;
            if ($rapportPath) unlink($uploadDir . basename($rapportPath));
        }
    }
}

// Récupérer les enseignants et étudiants (pour admin)
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants");
$etudiants = $conn->query("SELECT e.id, e.nom, e.prenom FROM etudiants e JOIN users u ON e.id = u.user_id WHERE u.role = 'etudiant'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter PFE</title>
    <link rel="stylesheet" href="../../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form method="post" enctype="multipart/form-data">
            <h2 class="login_h2">Ajouter un PFE</h2>

            <?php if (!empty($errors['db'])): ?>
                <p class="error-message"><?= $errors['db'] ?></p>
            <?php endif; ?>

            <?php if (!empty($errors['etudiant'])): ?>
                <p class="error-message"><?= $errors['etudiant'] ?></p>
            <?php endif; ?>

            <!-- Champs communs -->
            <input class="login_input" type="text" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" placeholder="Titre*" required>
            <?php if (!empty($errors['titre'])): ?>
                <small class="error-message"><?= $errors['titre'] ?></small>
            <?php endif; ?>

            <textarea class="login_input" name="resume" placeholder="Résumé"><?= htmlspecialchars($_POST['resume'] ?? '') ?></textarea>

            <input class="login_input" type="text" name="organisme" value="<?= htmlspecialchars($_POST['organisme'] ?? '') ?>" placeholder="Organisme*" required>
            <?php if (!empty($errors['organisme'])): ?>
                <small class="error-message"><?= $errors['organisme'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="encadrant_ex" value="<?= htmlspecialchars($_POST['encadrant_ex'] ?? '') ?>" placeholder="Encadrant externe*" required>
            <?php if (!empty($errors['encadrant_ex'])): ?>
                <small class="error-message"><?= $errors['encadrant_ex'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="email" name="email_ex" value="<?= htmlspecialchars($_POST['email_ex'] ?? '') ?>" placeholder="Email encadrant externe">

            <select class="login_select" name="encadrant_in_id" required>
                <option value="">Encadrant interne*</option>
                <?php while ($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>" <?= (($_POST['encadrant_in_id'] ?? '') == $ens['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($ens['nom'] . ' ' . $ens['prenom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- Sélection d'étudiant pour admin -->
            <?php if ($role === 'admin'): ?>
                <select class="login_select" name="etudiant_id" required>
                    <option value="">Sélectionner un étudiant*</option>
                    <?php while ($etu = $etudiants->fetch_assoc()): ?>
                        <option value="<?= $etu['id'] ?>" <?= (($_POST['etudiant_id'] ?? '') == $etu['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($etu['nom'] . ' ' . $etu['prenom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            <?php endif; ?>

            <input class="login_input" type="file" name="rapport" accept=".pdf" required>
            <?php if (!empty($errors['rapport'])): ?>
                <small class="error-message"><?= $errors['rapport'] ?></small>
            <?php endif; ?>

            <button class="login_button" type="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
