<?php
include('../../connexion_db.php');

// Récupérer tous les enseignants avec leurs départements
$sql = "SELECT e.*, d.nom as departement_nom 
        FROM enseignants e 
        LEFT JOIN departements d ON e.dept_id = d.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
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

        <h2 class="login_h2">Liste des Enseignants</h2>


        <div class="nav_links">
            <a href="add_enseignants.php" >
                <button class="p"> Ajouter un enseignant</button>
            </a>
        </div>
        <br>
        <br>

        <table class="crud_table">
            <thead class="p">
            <tr class="p">
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Département</th>
                <th>Modifier</th>
                <th>Suprimmer</th>
            </tr>
            </thead class="p">
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr class="p">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['departement_nom'] ?? 'Non affecté') ?></td>
                    <td>
                        <a href="edit_enseignants.php?id=<?= $row['id'] ?>" class="">
                            <button class="btn-edit"> Modifier </button>
                        </a>
                    </td>
                    <td>
                        <a href="rm_enseignants.php?id=<?= $row['id'] ?>"
                           class=""
                           onclick="return confirm('Confirmer la suppression ?')">
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


