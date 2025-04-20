<?php
session_start();
include('../connexion_db.php');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];


$req_info = $conn->query("SELECT * FROM enseignants WHERE id = $enseignant_id");
$enseignant = $req_info->fetch_assoc();


$req_pfes = $conn->query("
    SELECT p.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom 
    FROM pfes p 
    JOIN etudiants e ON p.etudiant_id = e.id 
    WHERE p.encadrant_in_id = $enseignant_id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE - Espace Enseignant</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="dashboard">
        <div class="logout">
            <a href="../logout.php" class="btn">Déconnexion</a>
        </div>

        <div class="welcome">
            <h2 class="login_h2">Bienvenue, <?= htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']) ?></h2>
        </div>

        <div class="pfe-list">
            <?php if ($req_pfes->num_rows > 0): ?>
                <?php while($pfe = $req_pfes->fetch_assoc()): ?>
                    <div class="pfe-card">
                        <h3><?= htmlspecialchars($pfe['titre']) ?></h3>
                        <p><strong>Étudiant:</strong> <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?></p>
                        <p><strong>Organisme:</strong> <?= htmlspecialchars($pfe['organisme']) ?></p>
                        <p><strong>Statut:</strong> <?= $pfe['rapport'] ? 'Complet' : 'En cours' ?></p>

                        <div class="pfe-actions">
                            <a href="consulter_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Consulter</a>
                            <a href="modifier_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Modifier</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="info-message">Vous n'encadrez actuellement aucun PFE.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
