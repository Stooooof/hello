<?php
session_start();
include('../connexion_db.php');


if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}


$email = $_SESSION['email'];
$user_query = $conn->query("SELECT u.*, e.nom, e.prenom, e.matricule 
                          FROM users u 
                          JOIN etudiants e ON u.user_id = e.id 
                          WHERE u.email = '$email' AND u.role = 'etudiant'");

if ($user_query->num_rows === 0) {
    header('Location: ../login.php');
    exit();
}

$user = $user_query->fetch_assoc();
$etudiant_id = $user['user_id'];


$pfes = $conn->query("SELECT * FROM pfes WHERE etudiant_id = $etudiant_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiant - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">

</head>
<body class="login_body">
<div class="Con">
    <div class="dashboard">
        <div class="logout">
            <a href="../logout.php" class="btn">Déconnexion</a>
        </div>

        <div class="welcome">
            <h2 class="login_h2">Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
            <p>Matricule: <?= htmlspecialchars($user['matricule']) ?></p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="add-pfe">
            <a href="add_pfe.php" class="btn btn-success">+ Ajouter un PFE</a>
        </div>

        <div class="pfe-list">
            <?php if ($pfes->num_rows > 0): ?>
                <?php while ($pfe = $pfes->fetch_assoc()): ?>
                    <div class="pfe-card">
                        <h3><?= htmlspecialchars($pfe['titre']) ?></h3>
                        <p><strong>Organisme:</strong> <?= htmlspecialchars($pfe['organisme']) ?></p>
                        <p><strong>Statut:</strong> <?= $pfe['rapport'] ? 'Complet' : 'En cours' ?></p>

                        <div class="pfe-actions">
                            <a href="view_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Voir</a>
                            <a href="edit_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Modifier</a>
                            <a href="confirm_delete.php?id=<?= $pfe['id'] ?>" class="error">Supprimer</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucun PFE enregistré pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
