<?php
include('../../connexion_db.php');

// Initialisation des variables
$errors = [];
$nom = '';
$chef_id = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $nom = trim($_POST['nom']);
    $chef_id = trim($_POST['chef_id']);

    // Validation
    if (empty($nom)) {
        $errors['nom'] = "Le nom du département est obligatoire";
    }

    // Vérifier si le département existe déjà
    $check_sql = "SELECT id FROM departements WHERE nom = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $nom);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $errors['submit'] = "Un département avec ce nom existe déjà";
    }

    // Si pas d'erreurs
    if (empty($errors)) {
        // Insertion dans la base
        $sql = "INSERT INTO departements (nom, chef_id)
                VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nom, $chef_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Département ajouté avec succès";
            header('Location: table_departements.php');
            exit();
        } else {
            $errors['db'] = "Erreur lors de l'ajout: " . $conn->error;
        }
    }
}

// Récupérer la liste des enseignants pour le select (chef de département)
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants");
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
            <h2 class="login_h2"> Ajouter un département </h2>

            <?php if (!empty($errors['submit'])): ?>
                <p class="error-message"><?= $errors['submit'] ?></p>
            <?php endif; ?>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Nom du département" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <select name="chef_id" class="login_select">
                <option value="">-- Sélectionnez un chef de département --</option>
                <?php while($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>" <?= ($chef_id == $ens['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ens['nom'] . ' ' . $ens['prenom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="login_button" type="submit" name="submit">Ajouter</button>
        </form>
    </div>
</div>
</body>
</html>
