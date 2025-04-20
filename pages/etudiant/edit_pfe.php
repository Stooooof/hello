<?php
session_start();
include('../connexion_db.php');

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}


$email = $_SESSION['email'];
$etudiant_query = $conn->query("SELECT id FROM etudiants WHERE email = '$email'");
if ($etudiant_query->num_rows === 0) {
    die("Profil étudiant non trouvé");
}
$etudiant = $etudiant_query->fetch_assoc();
$etudiant_id = $etudiant['id'];


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: etudiant.php');
    exit();
}
$pfe_id = (int)$_GET['id'];


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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);
    $encadrant_in_id = (int)$_POST['encadrant_in_id'];

    
    if (empty($titre)) $errors['titre'] = "Le titre est obligatoire";
    if (empty($organisme)) $errors['organisme'] = "L'organisme est obligatoire";
    if (empty($encadrant_ex)) $errors['encadrant_ex'] = "L'encadrant externe est obligatoire";
    if (empty($encadrant_in_id)) $errors['encadrant_in'] = "L'encadrant interne est obligatoire";

    
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
                
                if (!empty($rapportPath) && file_exists("C:/xampp/htdocs/" . $rapportPath)) {
                    unlink("C:/xampp/htdocs/" . $rapportPath);
                }
                $rapportPath = "pfe_uploads/" . $fileName;
            } else {
                $errors['rapport'] = "Erreur lors de l'enregistrement du fichier";
            }
        }
    }

   
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


$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier PFE - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form method="post" enctype="multipart/form-data" class="edit-pfe-form">
            <h2 class="login_h2">Modifier mon PFE</h2>
            

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

            <div>
                <label>Titre du PFE</label>
                <input class="login_input" type="text" name="titre" value="<?= htmlspecialchars($pfe['titre']) ?>" placeholder="Titre*" required>
            </div>

            <div>
                <label>Résumé</label>
                <textarea class="login_input" name="resume" placeholder="Résumé"><?= htmlspecialchars($pfe['resume']) ?></textarea>
            </div>

            <div>
                <label>Organisme</label>
                <input class="login_input" type="text" name="organisme" value="<?= htmlspecialchars($pfe['organisme']) ?>" placeholder="Organisme*" required>
            </div>

            <div>
                <label>Encadrant externe</label>
                <input class="login_input" type="text" name="encadrant_ex" value="<?= htmlspecialchars($pfe['encadrant_ex']) ?>" placeholder="Encadrant externe*" required>
            </div>

            <div>
                <label>Email encadrant externe</label>
                <input class="login_input" type="email" name="email_ex" value="<?= htmlspecialchars($pfe['email_ex']) ?>" placeholder="Email encadrant externe">
            </div>
            <div>
                <label>Encadrant interne</label>
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
                <label>Rapport de PFE (PDF, DOC, DOCX)</label>
                <input class="login_input" type="file" name="rapport" accept=".pdf,.doc,.docx">
                
                <?php if (!empty($pfe['rapport'])): ?>
                    <div class="current-file">
                        Fichier actuel: 
                        <a href="/<?= htmlspecialchars($pfe['rapport']) ?>" target="_blank">
                            <?= htmlspecialchars(basename($pfe['rapport'])) ?>
                        </a>
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'])): ?>
                            (<?= round(filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']) / 1024) ?> KB)
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="current-file">Aucun rapport actuellement.</div>
                <?php endif; ?>
                <p class="p">Laissez vide pour conserver le rapport actuel.</p>
            </div>

            <div class="form-actions">
                <button class="login_button" type="submit">Enregistrer</button>
                <a href="etudiant.php" class="login_button cancel">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>