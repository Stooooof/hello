<?php
session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? "",
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
    <div class="for" id="login-form">
        <form action="login_verif.php" method="post">
            <h2 class="login_h2">Connecter</h2>

            <?php if (!empty($errors['login'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['login']) ?></div>
            <?php endif; ?>

            <input class="login_input" type="email" name="email" placeholder="Email" required>
            <input class="login_input" type="password" name="password" placeholder="Password" required>
            <button class="login_button" type="submit" name="login">Connecter</button>

            <p class="p">Vous n'avez pas un compte ? <a href="register.php">Crée-le !</a></p>
        </form>
    </div>
</div>
</body>
</html>