<?php
session_start();

$errors = [
    'register' => $_SESSION['register_error'] ?? ""
];
session_unset();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
    <link rel="stylesheet" href="mystyles.css">
</head>



<body class="login_body">
    <div class="Con">
        <div class="for" id="register-form">
            <form action="register_verif.php" method="post">

            <h2 class="login_h2"> S'inscrire </h2>

                <?php if (!empty($errors['register'])): ?>
                <p class="error-message"><?= htmlspecialchars($errors['register']) ?></p>
                <?php endif; ?>

                <input class="login_input" type="email" name="email" placeholder="Email" required>
                <input class="login_input" type="password" name="password" placeholder="Password" required>
                <select class="login_select" name="role" required>
                    <option value=""> --Selectionner le role-- </option>
                    <option value="admin"> Admin </option>
                    <option value="enseignant"> Enseignant </option>
                    <option value="etudiant"> Etudiant </option>
                </select>
                
                <button class="login_button" type="submit" name="register"> Enregistrer </button>
                
                <p class="p"> Vous avez un compte ? <a href="login.php"> Connecter !! </a></p>
            </form>
        </div>
    
    
    
    
    </div>
</body>

</html>