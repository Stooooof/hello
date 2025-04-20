<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];

// Récupération des informations de l'enseignant
$req_info = $conn->query("SELECT * FROM enseignants WHERE id = $enseignant_id");
$enseignant = $req_info->fetch_assoc();

// Récupération des PFEs encadrés par l'enseignant
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
<body>


<div class="container">
    <h1>Bienvenue, <?= htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']) ?></h1>

    <section class="pfes-section">
        <h2>Mes PFEs encadrés</h2>

        <?php if ($req_pfes->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Titre</th>
                    <th>Organisme</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php while($pfe = $req_pfes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?></td>
                        <td><?= htmlspecialchars($pfe['titre']) ?></td>
                        <td><?= htmlspecialchars($pfe['organisme']) ?></td>
                        <td>
                            <a href="consulter_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-view">Consulter</a>
                            <a href="modifier_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-edit">Modifier</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="info-message">Vous n'encadrez actuellement aucun PFE.</p>
        <?php endif; ?>
    </section>
</div>


</body>
</html>
