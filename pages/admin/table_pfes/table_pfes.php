<?php
session_start();
include('../../connexion_db.php');

// Vérification de l'authentification
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

// Récupération du rôle de l'utilisateur
$email = $_SESSION['email'];
$user_query = $conn->query("SELECT role, user_id FROM users WHERE email = '$email'");
$user = $user_query->fetch_assoc();
$role = $user['role'];
$user_id = $user['user_id'];

// Construction de la requête selon le rôle
if ($role === 'admin') {
    $query = "SELECT p.*, e.nom as etudiant_nom, e.prenom as etudiant_prenom, 
              en.nom as encadrant_nom, en.prenom as encadrant_prenom
              FROM pfes p
              JOIN etudiants e ON p.etudiant_id = e.id
              LEFT JOIN enseignants en ON p.encadrant_in_id = en.id";
} elseif ($role === 'enseignant') {
    $query = "SELECT p.*, e.nom as etudiant_nom, e.prenom as etudiant_prenom
              FROM pfes p
              JOIN etudiants e ON p.etudiant_id = e.id
              WHERE p.encadrant_in_id = $user_id";
} elseif ($role === 'etudiant') {
    $query = "SELECT p.*, en.nom as encadrant_nom, en.prenom as encadrant_prenom
              FROM pfes p
              LEFT JOIN enseignants en ON p.encadrant_in_id = en.id
              WHERE p.etudiant_id = $user_id";
}

$result = $conn->query($query);

// Traitement de la suppression
if (isset($_GET['delete'])) {
    $pfe_id = (int)$_GET['delete'];

    // Récupérer le chemin du rapport avant suppression
    $file_query = $conn->query("SELECT rapport FROM pfes WHERE id = $pfe_id");
    $file = $file_query->fetch_assoc();

    if ($file['rapport']) {
        unlink("C:/xampp/htdocs/" . $file['rapport']);
    }

    $conn->query("DELETE FROM pfes WHERE id = $pfe_id");
    $_SESSION['success'] = "PFE supprimé avec succès";
    header('Location: table_pfes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des PFEs</title>
    <link rel="stylesheet" href="../../mystyles.css">
</head>
<body class="table_body">


    <div class="table-container">
        <div class="nav_links">
            <a href="../admin.php">
                <button class="p"> ⬅️ Acceuil </button>
            </a>
        </div>
        <br>
        <br>
        <h2 class="login_h2">Liste des Projets de Fin d'Études</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="nav_links">
            <a href="add_pfes.php">
                <button class="p">Ajouter un PFE</button>
            </a>
        </div>
        <br><br>

        <table class="crud_table">
            <thead class="p">
            <tr>
                <th>Titre</th>
                <th>Étudiant</th>
                <?php if ($role !== 'etudiant'): ?>
                    <th>Encadrant</th>
                <?php endif; ?>
                <th>Organisme</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($pfe = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($pfe['titre']) ?></td>

                    <td>
                        <?php if ($role !== 'etudiant'): ?>
                            <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?>
                        <?php else: ?>
                            Vous
                        <?php endif; ?>
                    </td>

                    <?php if ($role !== 'etudiant'): ?>
                        <td>
                            <?= $pfe['encadrant_prenom'] ? htmlspecialchars($pfe['encadrant_prenom'] . ' ' . $pfe['encadrant_nom']) : 'Non assigné' ?>
                        </td>
                    <?php endif; ?>

                    <td><?= htmlspecialchars($pfe['organisme']) ?></td>

                    <td>
                        <a href="view_pfes.php?id=<?= $pfe['id'] ?>" class="btn-edit">Voir</a>
                        <a href="edit_pfes.php?id=<?= $pfe['id'] ?>" class="btn-edit">Modifier</a>
                        <?php if ($role === 'admin' || ($role === 'etudiant' && empty($pfe['rapport']))): ?>
                            <a href="table_pfes.php?delete=<?= $pfe['id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce PFE?')">Supprimer</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>


</body>
</html>
