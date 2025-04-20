<?php
include("../../connect_ddb.php");

$enseignant_id = null;
$pfes = [];
$error = "";
$email_saisi = "";

// Si l'enseignant a soumis son email
if (isset($_GET["email"])) {
    $email_saisi = htmlspecialchars($_GET["email"]);

    // Vérification de l'existence de l'enseignant
    $stmt = $conn->prepare("SELECT id FROM enseignants WHERE email = ?");
    $stmt->bind_param("s", $email_saisi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Aucun enseignant trouvé avec cet email.";
    } else {
        $enseignant = $result->fetch_assoc();
        $enseignant_id = $enseignant["id"];

        // Récupération des PFEs encadrés
        $pfe_stmt = $conn->prepare("SELECT id, titre FROM pfes WHERE encadrant_in_id = ?");
        $pfe_stmt->bind_param("i", $enseignant_id);
        $pfe_stmt->execute();
        $pfe_result = $pfe_stmt->get_result();

        while ($row = $pfe_result->fetch_assoc()) {
            $pfes[] = $row;
        }

        if (empty($pfes)) {
            $error = "Aucun PFE encadré trouvé pour cet enseignant.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sélectionner un PFE à Modifier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f6fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #003366;
            color: white;
            padding: 20px;
            margin: 0;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        label {
            font-weight: bold;
            color: #003366;
        }

        input[type="email"], select {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            background-color: #003366;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #005599;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 15px;
            text-align: center;
        }

        .success {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<h1>Modifier un PFE Encadré</h1>

<div class="container">
    <form method="get" action="">
        <label for="email">Votre adresse email :</label>
        <input type="email" id="email" name="email" value="<?php echo $email_saisi; ?>" required>
        <button type="submit">Afficher mes PFEs</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php elseif ($enseignant_id && !empty($pfes)): ?>
        <form method="get" action="modifier_formulaire_pfe.php">
            <input type="hidden" name="email" value="<?php echo $email_saisi; ?>">
            <label for="pfe_id">Sélectionnez un PFE :</label>
            <select name="pfe_id" id="pfe_id" required>
                <?php foreach ($pfes as $pfe): ?>
                    <option value="<?php echo $pfe['id']; ?>">
                        [ID <?php echo $pfe['id']; ?>] - <?php echo htmlspecialchars($pfe['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Modifier ce PFE</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>