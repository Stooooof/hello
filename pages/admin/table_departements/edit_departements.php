<?php
include('../../connexion_db.php');

// 1. Récupérer l'ID du département
$id = $_GET['id'];

// 2. Récupérer les données du département
$sql = "SELECT * FROM departements WHERE id = $id";
$result = $conn->query($sql);
$departement = $result->fetch_assoc();

// 3. Récupérer la liste des enseignants (pour le chef de département)
$enseignants_result = $conn->query("SELECT id, nom, prenom FROM enseignants");
$enseignants = [];
while($row = $enseignants_result->fetch_assoc()) {
    $enseignants[] = $row;
}

// 4. Traitement du formulaire
if(isset($_POST['submit'])) {
    $nom = $_POST['nom'];
    $chef_id = $_POST['chef_id'] ?? null;

    // Validation
    $errors = [];
    if(empty($nom)) {
        $errors['nom'] = "Le nom du département est obligatoire";
    }

    // Si pas d'erreurs
    if(empty($errors)) {
        // Requête de mise à jour
        $sql = "UPDATE departements SET 
                nom = '$nom',
                chef_id = " . ($chef_id ? "'$chef_id'" : "NULL") . "
                WHERE id = $id";

        if($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Département modifié avec succès";
            header("Location: table_departements.php");
            exit();
        } else {
            $errors['db'] = "Erreur: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
    <link rel="stylesheet" href="../../mystyles.css">
</head>

<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form action="" method="post">
            <h2 class="login_h2"> Modifier le département </h2>

            <?php if(!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom"
                   value="<?= htmlspecialchars($departement['nom']) ?>"
                   placeholder="Nom du département" required>
            <?php if(!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <select name="chef_id" class="login_select">
                <option value="">-- Aucun chef --</option>
                <?php foreach($enseignants as $ens): ?>
                    <option value="<?= $ens['id'] ?>"
                        <?= ($ens['id'] == $departement['chef_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ens['nom'] . ' ' . $ens['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="login_button" type="submit" name="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
