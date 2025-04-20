<?php
session_start();
include('../../connexion_db.php');

// Vérification de l'authentification
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

$pfe_id = (int)$_GET['id'];
$query = "SELECT p.*, e.nom as etudiant_nom, e.prenom as etudiant_prenom 
          FROM pfes p JOIN etudiants e ON p.etudiant_id = e.id 
          WHERE p.id = $pfe_id";
$result = $conn->query($query);
$pfe = $result->fetch_assoc();

// Vérification des permissions
$user_query = $conn->query("SELECT role, user_id FROM users WHERE email = '{$_SESSION['email']}'");
$user = $user_query->fetch_assoc();

if ($user['role'] === 'etudiant' && $pfe['etudiant_id'] != $user['user_id']) {
    die("Accès non autorisé");
}

// Téléchargement du fichier
if (isset($_GET['download']) && $pfe['rapport']) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'];

    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        $_SESSION['error'] = "Le fichier n'existe pas sur le serveur";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails PFE</title>
    <link rel="stylesheet" href="../../mystyles.css">
    <style>
        .pfe-container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .pfe-field { margin-bottom: 15px; }
        .pfe-label { font-weight: bold; }
        .download-section { margin-top: 20px; padding: 15px; background: #f5f5f5; }
        .download-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .error { color: #f44336; }
    </style>
</head>
<body>


<div class="pfe-container">
    <h2><?= htmlspecialchars($pfe['titre']) ?></h2>

    <div class="pfe-field">
        <span class="pfe-label">Étudiant:</span>
        <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?>
    </div>

    <div class="pfe-field">
        <span class="pfe-label">Organisme:</span>
        <?= htmlspecialchars($pfe['organisme']) ?>
    </div>

    <div class="pfe-field">
        <span class="pfe-label">Résumé:</span>
        <div style="white-space: pre-line;"><?= htmlspecialchars($pfe['resume']) ?></div>
    </div>

    <div class="download-section">
        <?php if ($pfe['rapport']): ?>
            <p class="pfe-label">Rapport de PFE:</p>
            <a href="view_pfes.php?id=<?= $pfe_id ?>&download=1" class="download-btn">
                Télécharger le rapport (PDF)
            </a>
            <p>Taille: <?= round(filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']) / 1024) ?> KB</p>
        <?php else: ?>
            <p>Aucun rapport disponible</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?= $_SESSION['error'] ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>

    <a href="table_pfes.php" style="margin-top:20px;display:inline-block;">← Retour</a>
</div>


</body>
</html>
