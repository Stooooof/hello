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
$matricule = $nom = $prenom = $email = $tel = '';
$promotion = date('Y'); // Valeur par défaut = année courante
$fil_id = '';

// Récupérer la liste des étudiants sans profil complet (en utilisant email comme lien)
$etudiants_sans_profil = $conn->query("
    SELECT u.id, u.email 
    FROM users u
    WHERE u.role = 'etudiant' AND u.email NOT IN (
        SELECT email FROM etudiants
    )
");

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $user_id = trim($_POST['user_id']);
    $matricule = trim($_POST['matricule']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $promotion = trim($_POST['promotion']);
    $fil_id = trim($_POST['fil_id']);

    // Validation
    if (empty($user_id)) {
        $errors['user'] = "Vous devez sélectionner un étudiant";
    }

    if (empty($matricule)) {
        $errors['matricule'] = "Le matricule est obligatoire";
    }

    if (empty($nom)) {
        $errors['nom'] = "Le nom est obligatoire";
    }

    if (empty($prenom)) {
        $errors['prenom'] = "Le prénom est obligatoire";
    }

    // Si pas d'erreurs
    if (empty($errors)) {
        // Insertion dans la table etudiants
        $sql = "INSERT INTO etudiants (matricule, nom, prenom, email, tel, promotion, fil_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $matricule, $nom, $prenom, $email, $tel, $promotion, $fil_id);

        if ($stmt->execute()) {
            // Mettre à jour le user_id dans users si la colonne existe
            @$conn->query("UPDATE users SET user_id = ".$stmt->insert_id." WHERE id = $user_id");

            $_SESSION['success'] = "Profil étudiant complété avec succès";
            header('Location: table_etudiants.php');
            exit();
        } else {
            $errors['db'] = "Erreur lors de l'ajout: " . $conn->error;
        }
    }
}

// Récupérer la liste des filières pour le select
$filieres = $conn->query("SELECT id, nom FROM filieres");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compléter profil étudiant - SMART-PFE</title>
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
            <h2 class="login_h2">Compléter profil étudiant</h2>

            <?php if (!empty($errors['db'])): ?>
                <div class="error-message"><?= $errors['db'] ?></div>
            <?php endif; ?>

            <div class="info-box">
                <p>Cette interface permet de compléter le profil des étudiants déjà enregistrés dans le système.</p>
                <p>L'étudiant doit d'abord créer un compte via la page d'inscription.</p>
            </div>

            <select name="user_id" class="login_select" required>
                <option value="">-- Sélectionnez un étudiant --</option>
                <?php while($user = $etudiants_sans_profil->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['email']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['user'])): ?>
                <small class="error-message"><?= $errors['user'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="matricule" value="<?= htmlspecialchars($matricule) ?>" placeholder="Matricule" required>
            <?php if (!empty($errors['matricule'])): ?>
                <small class="error-message"><?= $errors['matricule'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="number" name="promotion" value="<?= htmlspecialchars($promotion) ?>" placeholder="Promotion" required>

            <input class="login_input" type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Nom" required>
            <?php if (!empty($errors['nom'])): ?>
                <small class="error-message"><?= $errors['nom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Prénom" required>
            <?php if (!empty($errors['prenom'])): ?>
                <small class="error-message"><?= $errors['prenom'] ?></small>
            <?php endif; ?>

            <input class="login_input" type="tel" name="tel" value="<?= htmlspecialchars($tel) ?>" placeholder="Téléphone">

            <input class="login_input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>

            <select name="fil_id" class="login_select" required>
                <option value="">-- Sélectionnez une filière --</option>
                <?php while($filiere = $filieres->fetch_assoc()): ?>
                    <option value="<?= $filiere['id'] ?>" <?= (isset($_POST['fil_id']) && $_POST['fil_id'] == $filiere['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($filiere['nom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="login_button" type="submit">Compléter le profil</button>
        </form>
    </div>
</div>
</body>
</html>