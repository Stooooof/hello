<?php
session_start();
include('../../connexion_db.php');

// Vérification de l'authentification
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

// Récupération du PFE à modifier
$pfe_id = (int)$_GET['id'];
$query = "SELECT * FROM pfes WHERE id = $pfe_id";
$result = $conn->query($query);
$pfe = $result->fetch_assoc();

// Vérification des permissions
$user_query = $conn->query("SELECT role, user_id FROM users WHERE email = '{$_SESSION['email']}'");
$user = $user_query->fetch_assoc();
$uploadDir = "C:/xampp/htdocs/pfe_uploads/";

if ($user['role'] === 'etudiant' && $pfe['etudiant_id'] != $user['user_id']) {
    die("Accès non autorisé");
}
if ($user['role'] === 'enseignant' && $pfe['encadrant_in_id'] != $user['user_id']) {
    die("Accès non autorisé");
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);
    $encadrant_in_id = (int)$_POST['encadrant_in_id'];

    // Gestion du fichier
    $rapportPath = $pfe['rapport'];
    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] == 0) {
        $fileExt = strtolower(pathinfo($_FILES['rapport']['name'], PATHINFO_EXTENSION));
        if ($fileExt === 'pdf') {
            $fileName = uniqid() . '.pdf';
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $targetPath)) {
                // Supprimer l'ancien fichier s'il existe
                if ($rapportPath && file_exists("C:/xampp/htdocs/" . $rapportPath)) {
                    unlink("C:/xampp/htdocs/" . $rapportPath);
                }
                $rapportPath = "pfe_uploads/" . $fileName;
            }
        }
    }

    $sql = "UPDATE pfes SET 
            titre = ?, 
            resume = ?, 
            organisme = ?, 
            encadrant_ex = ?, 
            email_ex = ?, 
            encadrant_in_id = ?, 
            rapport = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssisi", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $encadrant_in_id, $rapportPath, $pfe_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "PFE mis à jour avec succès";
        header("Location: view_pfes.php?id=$pfe_id");
        exit();
    }
}

// Récupérer les enseignants pour le select
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier PFE</title>
    <link rel="stylesheet" href="../../mystyles.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .current-file {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>


<div class="edit-container">
    <h2>Modifier le PFE</h2>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titre">Titre*</label>
            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($pfe['titre']) ?>" required>
        </div>

        <div class="form-group">
            <label for="resume">Résumé</label>
            <textarea id="resume" name="resume" rows="5"><?= htmlspecialchars($pfe['resume']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="organisme">Organisme*</label>
            <input type="text" id="organisme" name="organisme" value="<?= htmlspecialchars($pfe['organisme']) ?>" required>
        </div>

        <div class="form-group">
            <label for="encadrant_ex">Encadrant externe*</label>
            <input type="text" id="encadrant_ex" name="encadrant_ex" value="<?= htmlspecialchars($pfe['encadrant_ex']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email_ex">Email encadrant externe</label>
            <input type="email" id="email_ex" name="email_ex" value="<?= htmlspecialchars($pfe['email_ex']) ?>">
        </div>

        <div class="form-group">
            <label for="encadrant_in_id">Encadrant interne*</label>
            <select id="encadrant_in_id" name="encadrant_in_id" required>
                <option value="">-- Sélectionner --</option>
                <?php while ($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>" <?= $pfe['encadrant_in_id'] == $ens['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ens['nom'] . ' ' . $ens['prenom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="rapport">Rapport (PDF)</label>
            <input type="file" id="rapport" name="rapport" accept=".pdf">
            <?php if ($pfe['rapport']): ?>
                <div class="current-file">
                    Fichier actuel: <?= basename($pfe['rapport']) ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="login_button">Enregistrer</button>
        <a href="view_pfes.php?id=<?= $pfe_id ?>" class="back-link">Annuler</a>
    </form>
</div>

</body>
</html>
