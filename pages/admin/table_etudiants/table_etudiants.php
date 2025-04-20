<?php
include('../../connexion_db.php');

$sql = "SELECT e.*, f.nom as filiere_nom 
        FROM etudiants e 
        LEFT JOIN filieres f ON e.fil_id = f.id";
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


        <h2 class="login_h2">Liste des Étudiants</h2>

        <div class="nav_links">
            <a href="add_etudiants.php" >
                <button class="p"> Ajouter un étudiant </button>
            </a>
        </div>
        <br>
        <br>

        <table class="crud_table">
            <thead class="p">
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Filière</th>
                <th>Modifier</th>
                <th>Supprimer</th>
            </tr>
            </thead>
            <tbody class="p">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['matricule']) ?></td>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['filiere_nom'] ?? 'Non affecté') ?></td>
                    <td>
                        <a href="edit_etudiants.php?id=<?= $row['id'] ?>" class="">
                            <button class="btn-edit"> Modifier </button>
                        </a>
                    </td>
                    <td>
                        <a href="rm_etudiants.php?id=<?= $row['id'] ?>"
                           class=""
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')">
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
































