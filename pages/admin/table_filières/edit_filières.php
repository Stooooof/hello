<?php
include('../../connexion_db.php');


$id = $_GET['id'];


$sql = "SELECT * FROM filieres WHERE id = $id";
$result = $conn->query($sql);
$filiere = $result->fetch_assoc();

if (!$filiere) {
    header('Location: table_filières.php');
    exit();
}


$departements = $conn->query("SELECT id, nom FROM departements ORDER BY nom");
$enseignants = $conn->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom, prenom");


if (isset($_POST['submit'])) {
    $nom = $_POST['nom'];
    $dept_id = $_POST['dept_id'];
    $coord_id = $_POST['coord_id'] ?: null;

    $errors = [];
    if (empty($nom)) $errors['nom'] = "Nom requis";
    if (empty($dept_id)) $errors['dept_id'] = "Département requis";

    if (empty($errors)) {
        $sql = "UPDATE filieres SET 
                nom = ?,
                dept_id = ?,
                coord_id = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $nom, $dept_id, $coord_id, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Filière mise à jour avec succès";
            header("Location: table_filières.php");
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
    <title>Modifier Filière</title>
    <link rel="icon" href="../../../img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form action="" method="post">
            <h2 class="login_h2">Modifier la Filière</h2>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom"
                   value="<?= htmlspecialchars($filiere['nom']) ?>"
                   placeholder="Nom de la filière" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <select name="dept_id" class="login_select" required>
                <option value="">-- Sélectionnez un département --</option>
                <?php while($dept = $departements->fetch_assoc()): ?>
                    <option value="<?= $dept['id'] ?>"
                        <?= ($dept['id'] == $filiere['dept_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['nom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['dept_id'])): ?>
                <small class="error-message"><?= $errors['dept_id'] ?></small>
            <?php endif; ?>

            <select name="coord_id" class="login_select">
                <option value="">-- Aucun coordinateur --</option>
                <?php while($ens = $enseignants->fetch_assoc()): ?>
                    <option value="<?= $ens['id'] ?>"
                        <?= ($ens['id'] == $filiere['coord_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ens['nom'] . ' ' . $ens['prenom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="login_button" type="submit" name="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
