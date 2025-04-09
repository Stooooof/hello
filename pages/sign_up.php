<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="../pages/styles.css">
</head>



<body class="login_body">

<form class="login_form">

    <div class="login_flex-column">
        <label> Nom Complet </label>
    </div>
    <div class="login_inputForm">
        <input name="name" placeholder="Enter votre nom complet" class="login_input" type="text">
    </div>

    <div class="login_flex-column">
        <label> Choisissez un rôle : </label>
    </div>
    <div class="login_inputForm">
        <select name="role" >
            <option value="étudiant"> Étudiant </option>
            <option value="prof"> Prof </option>
            <option value="admin"> Admin </option>
        </select>
    </div>

    <div class="login_flex-column">
        <label>Email</label>
    </div>
    <div class="login_inputForm">
        <input name="email" placeholder="Enter votre Email" class="login_input" type="email">
    </div>

    <div class="login_flex-column">
        <label>Password</label>
    </div>
    <div class="login_inputForm">
        <input name="password" placeholder="Enter votre Password" class="login_input" type="password">
    </div>

    <button class="login_button-submit">Connexion</button>

</form>
</body>

</html>