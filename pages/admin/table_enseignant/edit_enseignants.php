<?php
include('../../connexion_db.php');

$id = $_GET['id'];

$sql = "SELECT * FROM enseignants WHERE id = $id";
$result = $conn->query($sql);
$enseignant = $result->fetch_assoc();

$departements_result = $conn->query("SELECT * FROM departements");
$departements = [];
while($row = $departements_result->fetch_assoc()) {
    $departements[] = $row;
}

if(isset($_POST['submit'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $dept_id = $_POST['dept_id'];

    $sql = "UPDATE enseignants SET 
            nom = '$nom',
            prenom = '$prenom',
            email = '$email',
            dept_id = '$dept_id'
            WHERE id = $id";

    if($conn->query($sql) === TRUE) {
        header("Location: table_enseignants.php");
        exit();
    } else {
        echo "Erreur: " . $conn->error;
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
            <h2 class="login_h2"> Modifier un enseignant </h2>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom" value="<?php echo $enseignant['nom']; ?>" placeholder="Nom" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="prenom" value="<?php echo $enseignant['prenom']; ?>" placeholder="Prénom" required>
            <?php if (!empty($errors['prenom'])): ?>
                <small class="error-message"><?= $errors['prenom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="email" name="email" value="<?php echo $enseignant['email']; ?>" placeholder="Email" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error-message"><?= $errors['email'] ?></small>
            <?php endif; ?>

            <select name="dept_id" class="login_select">
                <option value="">-- Sélectionnez un département --</option>
                <?php foreach($departements as $dept): ?>
                    <option value="<?= $dept['id'] ?>"
                        <?= ($dept['id'] == $enseignant['dept_id']) ? 'selected' : '' ?>>
                        <?= $dept['nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="login_button" type="submit" name="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>