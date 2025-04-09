<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="../pages/styles.css">
</head>
<body class="login_body">

<form action="login_verif.php" method="post" class="login_form">
    <div class="login_flex-column">
        <label>Email</label>
    </div>
    <div class="login_inputForm">
        <input placeholder="Enter your Email" name="email" class="login_input" type="email">
    </div>

    <div class="login_flex-column">
        <label>Password</label>
    </div>
    <div class="login_inputForm">
        <input placeholder="Enter your Password" name="password" class="login_input" type="password">
    </div>

    <button class="login_button-submit">Sign In</button>

    <p class="p">Vous n'avez pas un compte ? <a href="../../PROJET_PFE/pages/sign_up.php" class="login_span"> Crée-le !</a></p>
</form>



</body>

</html>