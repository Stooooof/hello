<?php
session_start();
include('../connexion_db.php');

// Vérification complète de l'authentification
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}

// Récupérer l'ID étudiant depuis la table etudiants
$email = $_SESSION['email'];
$etudiant_query = $conn->query("SELECT id FROM etudiants WHERE email = '$email'");
if ($etudiant_query->num_rows === 0) {
    die("Profil étudiant non trouvé");
}
$etudiant = $etudiant_query->fetch_assoc();
$etudiant_id = $etudiant['id'];

// Vérification du paramètre ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: etudiant.php');
    exit();
}
$pfe_id = (int)$_GET['id'];

// Vérifier que le PFE appartient bien à l'étudiant
$pfe_query = $conn->prepare("SELECT * FROM pfes WHERE id = ? AND etudiant_id = ?");
$pfe_query->bind_param("ii", $pfe_id, $etudiant_id);
$pfe_query->execute();
$pfe = $pfe_query->get_result()->fetch_assoc();

if (!$pfe) {
    $_SESSION['error'] = "PFE non trouvé ou accès non autorisé";
    header('Location: etudiant.php');
    exit();
}

$uploadDir = "C:/xampp/htdocs/pfe_uploads/";
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);
    $encadrant_in_id = (int)$_POST['encadrant_in_id'];

    // Validation des champs obligatoires
    if (empty($titre)) $errors['titre'] = "Le titre est obligatoire";
    if (empty($organisme)) $errors['organisme'] = "L'organisme est obligatoire";
    if (empty($encadrant_ex)) $errors['encadrant_ex'] = "L'encadrant externe est obligatoire";
    if (empty($encadrant_in_id)) $errors['encadrant_in'] = "L'encadrant interne est obligatoire";

    // Gestion du fichier
    $rapportPath = $pfe['rapport'];
    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] === 0) {
        $fileExt = strtolower(pathinfo($_FILES['rapport']['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'pdf') {
            $errors['rapport'] = "Seuls les fichiers PDF sont acceptés";
        } elseif ($_FILES['rapport']['size'] > 5000000) { // 5MB max
            $errors['rapport'] = "Le fichier est trop volumineux (max 5MB)";
        } else {
            $fileName = uniqid() . '.pdf';
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $targetPath)) {
                // Supprimer l'ancien fichier s'il existe
                if (!empty($rapportPath) && file_exists("C:/xampp/htdocs/" . $rapportPath)) {
                    unlink("C:/xampp/htdocs/" . $rapportPath);
                }
                $rapportPath = "pfe_uploads/" . $fileName;
            } else {
                $errors['rapport'] = "Erreur lors de l'enregistrement du fichier";
            }
        }
    }

    // Mise à jour si aucune erreur
    if (empty($errors)) {
        $sql = "UPDATE pfes SET 
                titre = ?, 
                resume = ?, 
                organisme = ?, 
                encadrant_ex = ?, 
                email_ex = ?, 
                encadrant_in_id = ?, 
                rapport = ?
                WHERE id = ? AND etudiant_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssisii", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $encadrant_in_id, $rapportPath, $pfe_id, $etudiant_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "PFE mis à jour avec succès";
            header("Location: view_pfe.php?id=$pfe_id");
            exit();
        } else {
            $errors['db'] = "Erreur lors de la mise à jour: " . $conn->error;
        }
    }
}

// Récupérer la liste des enseignants
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .current-file {
            font-size: 0.9em;
            color: #666;
            margin: 10px 0;
        }
        .login_textarea {
            min-height: 150px;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body class="login_body">
<div class="Con">
    <div class="edit-container">
        <form method="post" enctype="multipart/form-data">
            <h2 class="login_h2">Modifier mon PFE</h2>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <div>
                <input class="login_input" type="text" name="titre" value="<?= htmlspecialchars($pfe['titre']) ?>" placeholder="Titre*" required>
                <?php if (!empty($errors['titre'])): ?>
                    <span class="error-message"><?= $errors['titre'] ?></span>
                <?php endif; ?>
            </div>

            <textarea class="login_textarea" name="resume" placeholder="Résumé"><?= htmlspecialchars($pfe['resume']) ?></textarea>

            <div>
                <input class="login_input" type="text" name="organisme" value="<?= htmlspecialchars($pfe['organisme']) ?>" placeholder="Organisme*" required>
                <?php if (!empty($errors['organisme'])): ?>
                    <span class="error-message"><?= $errors['organisme'] ?></span>
                <?php endif; ?>
            </div>

            <div>
                <input class="login_input" type="text" name="encadrant_ex" value="<?= htmlspecialchars($pfe['encadrant_ex']) ?>" placeholder="Encadrant externe*" required>
                <?php if (!empty($errors['encadrant_ex'])): ?>
                    <span class="error-message"><?= $errors['encadrant_ex'] ?></span>
                <?php endif; ?>
            </div>

            <input class="login_input" type="email" name="email_ex" value="<?= htmlspecialchars($pfe['email_ex']) ?>" placeholder="Email encadrant externe">

            <div>
                <select class="login_select" name="encadrant_in_id" required>
                    <option value="">-- Sélectionnez votre encadrant interne --</option>
                    <?php while ($ens = $enseignants->fetch_assoc()): ?>
                        <option value="<?= $ens['id'] ?>" <?= $pfe['encadrant_in_id'] == $ens['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if (!empty($errors['encadrant_in'])): ?>
                    <span class="error-message"><?= $errors['encadrant_in'] ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label style="display: block; margin: 15px 0 5px; font-weight: bold;">Rapport de PFE (PDF)</label>
                <input class="login_input" type="file" name="rapport" accept=".pdf">
                <?php if (!empty($errors['rapport'])): ?>
                    <span class="error-message"><?= $errors['rapport'] ?></span>
                <?php endif; ?>

                <?php if (!empty($pfe['rapport'])): ?>
                    <div class="current-file">
                        Fichier actuel: <?= basename($pfe['rapport']) ?>
                        <?php if (file_exists("C:/xampp/htdocs/" . $pfe['rapport'])): ?>
                            (<?= round(filesize("C:/xampp/htdocs/" . $pfe['rapport']) / 1024) ?> KB)
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button class="login_button" type="submit">Enregistrer</button>
                <a href="view_pfe.php?id=<?= $pfe_id ?>" class="login_button" style="background: #6c757d;">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>