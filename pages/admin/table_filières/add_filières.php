<?php
include('../../connexion_db.php');

$errors = [];
$nom = '';
$dept_id = '';
$coord_id = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $dept_id = trim($_POST['dept_id']);
    $coord_id = trim($_POST['coord_id']) ?: null;

    if (empty($nom)) {
        $errors['nom'] = "Le nom de la filière est obligatoire";
    }

    if (empty($dept_id)) {
        $errors['dept_id'] = "Le département est obligatoire";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO filieres (nom, dept_id, coord_id) 
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $nom, $dept_id, $coord_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Filière ajoutée avec succès";
            header('Location: table_filières.php');
            exit();
        } else {
            $errors['db'] = "Erreur lors de l'ajout: " . $conn->error;
        }
    }
}

$departements = $conn->query("SELECT id, nom FROM departements ORDER BY nom");

$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom, prenom");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Filière</title>
    <link rel="icon" href="../../../img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form action="" method="post">
            <h2 class="login_h2">Ajouter une Filière</h2>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom" value="<?= htmlspecialchars($nom) ?>"
                   placeholder="Nom de la filière" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <select name="dept_id" class="login_select" required>
                <option value="">-- Sélectionnez un département --</option>
                <?php while($dept = $departements->fetch_assoc()): ?>
                    <option value="<?= $dept['id'] ?>" <?= ($dept_id == $dept['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['nom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['dept_id'])): ?>
                <small class="error-message"><?= $errors['dept_id'] ?></small>
            <?php endif; ?>

            <select name="coord_id" class="login_select">
                <option value="">-- Sélectionnez un coordinateur ("optionnel") -- </option>
                <?php while($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>" <?= ($coord_id == $ens['id']) ? 'selected' : '' ?>>
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
