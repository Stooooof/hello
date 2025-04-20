<?php
include('../../connexion_db.php');

$id = $_GET['id'];

$sql = "SELECT * FROM etudiants WHERE id = $id";
$result = $conn->query($sql);
$etudiant = $result->fetch_assoc();

$filieres_result = $conn->query("SELECT * FROM filieres");
$filieres = [];
while($row = $filieres_result->fetch_assoc()) {
    $filieres[] = $row;
}

if(isset($_POST['submit'])) {
    $matricule = $_POST['matricule'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $promotion = $_POST['promotion'];
    $fil_id = $_POST['fil_id'];

    $sql = "UPDATE etudiants SET 
            matricule = '$matricule',
            nom = '$nom',
            prenom = '$prenom',
            email = '$email',
            tel = '$tel',
            promotion = '$promotion',
            fil_id = '$fil_id'
            WHERE id = $id";

    if($conn->query($sql) === TRUE) {
        header("Location: table_etudiants.php");
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
            <h2 class="login_h2"> Modifier un étudiant </h2>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>


            <input class="login_input" type="text" name="matricule" value="<?php echo $etudiant['matricule']; ?>" placeholder="Matricule" required>
            <?php if (!empty($errors['matricule'])): ?>
                <small class="error-message"><?= $errors['matricule'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="number" name="promotion" value="<?php echo $etudiant['promotion']; ?>" placeholder="Promotion" required>

            <input class="login_input" type="text" name="nom" value="<?php echo $etudiant['nom']; ?>" placeholder="Nom" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="test" name="prenom" value="<?php echo $etudiant['prenom']; ?>" placeholder="Prenom" required>
            <?php if (!empty($errors['prenom'])): ?>
                <small class="error-message"><?= $errors['prenom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="number" name="tel" value="<?php echo $etudiant['tel']; ?>" placeholder="Tel" required>


            <input class="login_input" type="email" name="email" value="<?php echo $etudiant['email']; ?>" placeholder="Email" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error-message"><?= $errors['email'] ?></small>
            <?php endif; ?>


            <select name="fil_id" class="login_select">
                <option value="">-- Sélectionnez une filière --</option>
                <?php foreach($filieres as $filiere): ?>
                    <option value="<?= $filiere['id'] ?>"
                        <?= ($filiere['id'] == $etudiant['fil_id']) ? 'selected' : '' ?>>
                        <?= $filiere['nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="login_button" type="submit" name="submit"> Enregistrer </button>

        </form>
    </div>



</div>
</body>


</html>
