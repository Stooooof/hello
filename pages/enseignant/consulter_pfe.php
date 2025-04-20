<?php
session_start();
include('../connexion_db.php');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: enseignant.php");
    exit();
}

$pfe_id = $_GET['id'];


$req_pfe = $conn->query("
    SELECT p.*, 
           e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.email AS etudiant_email, e.matricule,
           f.nom AS filiere_nom, 
           d.nom AS departement_nom
    FROM pfes p 
    JOIN etudiants e ON p.etudiant_id = e.id 
    JOIN filieres f ON e.fil_id = f.id
    JOIN departements d ON f.dept_id = d.id
    WHERE p.id = $pfe_id AND p.encadrant_in_id = $enseignant_id
");

if ($req_pfe->num_rows === 0) {
    header("Location: enseignant.php");
    exit();
}

$pfe = $req_pfe->fetch_assoc();


if (isset($_GET['download']) && $pfe['rapport']) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'];

    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        $_SESSION['error'] = "Le fichier n'existe pas sur le serveur";
        header("Location: consulter_pfe.php?id=$pfe_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consulter PFE - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="pfe-details consult-pfe-container">
        <h2 class="login_h2"><?= htmlspecialchars($pfe['titre']) ?></h2>

        <div class="detail-row">
            <span class="detail-label">Étudiant:</span>
            <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Matricule:</span>
            <?= htmlspecialchars($pfe['matricule']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <?= htmlspecialchars($pfe['etudiant_email']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Filière:</span>
            <?= htmlspecialchars($pfe['filiere_nom']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Département:</span>
            <?= htmlspecialchars($pfe['departement_nom']) ?>
        </div>

        <div class="detail-row">
            <span class="detail-label">Organisme:</span>
            <?= htmlspecialchars($pfe['organisme']) ?>
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
            <div class="resume-text"><?= htmlspecialchars($pfe['resume']) ?></div>
        </div>

        <?php if (!empty($pfe['rapport'])): ?>
            <div class="detail-row">
                <span class="detail-label">Rapport:</span>
                <a href="consulter_pfe.php?id=<?= $pfe_id ?>&download=1" class="download-btn">
                    Télécharger le rapport
                </a>
                
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="actions">
            <a href="modifier_pfe.php?id=<?= $pfe['id'] ?>" class="login_button">Modifier</a>
            <a href="enseignant.php" class="login_button cancel">Retour</a>
        </div>
    </div>
</div>
</body>
</html>
