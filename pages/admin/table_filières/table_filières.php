<?php
include('../../connexion_db.php');
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Filières</title>
    <link rel="icon" href="../../../img/icon.png" type="image/x-icon">
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

    <h2 class="login_h2">Liste des Filières</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="nav_links">
        <a href="add_filières.php">
            <button class="p">Ajouter une filière</button>
        </a>
    </div>
    <br><br>

    <?php
    $sql = "SELECT f.*, d.nom as departement_nom, 
            CONCAT(e.nom, ' ', e.prenom) as coordonnateur_nom
            FROM filieres f
            LEFT JOIN departements d ON f.dept_id = d.id
            LEFT JOIN enseignants e ON f.coord_id = e.id
            ORDER BY f.nom";
    $result = $conn->query($sql);
    ?>

    <table class="crud_table">
        <thead>
        <tr class="p">
            <th>ID</th>
            <th>Nom</th>
            <th>Département</th>
            <th>Coordonnateur</th>
            <th>Modifier</th>
            <th>Supprimer</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr class="p">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= htmlspecialchars($row['departement_nom']) ?></td>
                <td><?= htmlspecialchars($row['coordonnateur_nom'] ?? 'Non défini') ?></td>
                <td>
                    <a href="edit_filières.php?id=<?= $row['id'] ?>" class="">
                        <button class="btn-edit"> Modifier </button>
                    </a>
                </td>
                <td>
                    <a href="rm_filières.php?id=<?= $row['id'] ?>"
                       class=""
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer la filière <?= addslashes($row['nom']) ?> ?')">
                        <button class="btn-delete"> Supprimer </button>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
