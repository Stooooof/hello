<?php
include('../../connexion_db.php');
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Initialisation des variables
$errors = [];
$nom = $prenom = $email = '';
$dept_id = '';

// Récupérer la liste des enseignants sans profil complet (basé sur email)
$enseignants_sans_profil = $conn->query("
    SELECT u.id, u.email 
    FROM users u
    WHERE u.role = 'enseignant' AND u.email NOT IN (
        SELECT email FROM enseignants
    )
");

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $user_id = trim($_POST['user_id']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $dept_id = trim($_POST['dept_id']);

    // Validation
    if (empty($user_id)) {
        $errors['user'] = "Vous devez sélectionner un enseignant";
    }

    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire";
    }

    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire";
    }

    if (empty($email)) {
        $errors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    }

    // Si pas d'erreurs
    if (empty($errors)) {
        // Insertion dans la table enseignants (sans user_id)
        $sql = "INSERT INTO enseignants (nom, prenom, email, dept_id)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nom, $prenom, $email, $dept_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profil enseignant complété avec succès";
            header('Location: table_enseignants.php');
            exit();
        } else {
            $errors['db'] = "Erreur lors de l'ajout: " . $conn->error;
        }
    }
}

// Récupérer la liste des départements pour le select
$departements = $conn->query("SELECT id, nom FROM departements");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compléter profil enseignant - SMART-PFE</title>
    <link rel="stylesheet" href="../../mystyles.css">
    <style>
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form action="" method="post">
            <h2 class="login_h2">Compléter profil enseignant</h2>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <div class="info-box">
                <p>Cette interface permet de compléter le profil des enseignants déjà enregistrés dans le système.</p>
                <p>L'enseignant doit d'abord créer un compte via la page d'inscription.</p>
            </div>

            <select name="user_id" class="login_select" required>
                <option value="">-- Sélectionnez un enseignant --</option>
                <?php while($user = $enseignants_sans_profil->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['email']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['user'])): ?>
                <small class="error-message"><?= $errors['user'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Nom" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Prénom" required>
            <?php if (!empty($errors['prenom'])): ?>
                <small class="error-message"><?= $errors['prenom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error-message"><?= $errors['email'] ?></small>
            <?php endif; ?>

            <select name="dept_id" class="login_select" required>
                <option value="">-- Sélectionnez un département --</option>
                <?php while($dept = $departements->fetch_assoc()): ?>
                    <option value="<?= $dept['id'] ?>" <?= (isset($_POST['dept_id']) && $_POST['dept_id'] == $dept['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['nom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="login_button" type="submit">Compléter le profil</button>
        </form>
    </div>
</div>
</body>
</html>

