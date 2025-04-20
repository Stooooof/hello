<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}

// Récupérer l'ID de l'étudiant depuis la table etudiants (pas users)
$email = $_SESSION['email'];
$etudiant_query = $conn->query("SELECT id FROM etudiants WHERE email = '$email'");
if ($etudiant_query->num_rows === 0) {
    die("Profil étudiant non trouvé");
}
$etudiant = $etudiant_query->fetch_assoc();
$etudiant_id = $etudiant['id'];

// Vérifier que l'ID PFE est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: etudiant.php');
    exit();
}
$pfe_id = (int)$_GET['id'];

// Requête sécurisée avec jointure
$query = "SELECT p.*, e.nom as encadrant_nom, e.prenom as encadrant_prenom 
          FROM pfes p
          LEFT JOIN enseignants e ON p.encadrant_in_id = e.id
          WHERE p.id = ? AND p.etudiant_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $pfe_id, $etudiant_id);
$stmt->execute();
$result = $stmt->get_result();
$pfe = $result->fetch_assoc();

if (!$pfe) {
    $_SESSION['error'] = "PFE non trouvé ou accès non autorisé";
    header('Location: etudiant.php');
    exit();
}

// Téléchargement du fichier
if (isset($_GET['download']) && !empty($pfe['rapport'])) {
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
    <title>Détails du PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
    <style>
        .pfe-details {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-row {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .detail-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
        .download-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .error-message {
            color: #f44336;
            margin: 15px 0;
        }
    </style>
</head>
<body class="login_body">
<div class="Con">
    <div class="pfe-details">
        <h2 class="login_h2"><?= htmlspecialchars($pfe['titre']) ?></h2>

        <div class="detail-row">
            <span class="detail-label">Organisme:</span>
            <?= htmlspecialchars($pfe['organisme']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Encadrant interne:</span>
            <?= $pfe['encadrant_prenom'] ? htmlspecialchars($pfe['encadrant_prenom'].' '.$pfe['encadrant_nom']) : 'Non spécifié' ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Encadrant externe:</span>
            <?= htmlspecialchars($pfe['encadrant_ex']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email encadrant externe:</span>
            <?= htmlspecialchars($pfe['email_ex'] ?? 'Non spécifié') ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Résumé:</span>
            <div style="white-space: pre-line;"><?= htmlspecialchars($pfe['resume']) ?></div>
        </div>

        <?php if ($pfe['rapport']): ?>
            <div class="detail-row">
                <span class="detail-label">Rapport:</span>
                <a href="view_pfe.php?id=<?= $pfe_id ?>&download=1" class="download-btn">
                    Télécharger le rapport
                </a>
                <span style="margin-left: 10px;">
                (<?= round(filesize($_SERVER['DOCUMENT_ROOT'].'/'.$pfe['rapport'])/1024 )?> KB)
            </span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="actions">
            <a href="edit_pfe.php?id=<?= $pfe_id ?>" class="login_button">Modifier</a>
            <a href="etudiant.php" class="login_button" style="background: #6c757d;">Retour</a>
        </div>
    </div>
</div>
</body>
</html>