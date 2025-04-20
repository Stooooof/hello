<?php
include("../../connect_ddb.php");

if (!isset($_GET["pfe_id"])) {
    echo "<p class='error'>Aucun ID de PFE fourni.</p>";
    exit;
}

$pfe_id = intval($_GET["pfe_id"]);

$stmt = $conn->prepare("SELECT titre, resume, organisme, encadrant_ex, email_ex, rapport FROM pfes WHERE id = ?");
$stmt->bind_param("i", $pfe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='error'>PFE introuvable.</p>";
    exit;
}

$pfe = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le PFE</title>
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
            max-width: 700px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #003366;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        button {
            margin-top: 25px;
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
            text-align: center;
            margin-top: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Modifier le PFE</h1>
<div class="container">
    <form method="POST" action="enregistrer_modification_pfe.php" enctype="multipart/form-data">
        <input type="hidden" name="pfe_id" value="<?php echo $pfe_id; ?>">

        <label>Titre :</label>
        <input type="text" name="titre" value="<?php echo htmlspecialchars($pfe['titre']); ?>" required>

        <label>Résumé :</label>
        <textarea name="resume" rows="5"><?php echo htmlspecialchars($pfe['resume']); ?></textarea>

        <label>Organisme :</label>
        <input type="text" name="organisme" value="<?php echo htmlspecialchars($pfe['organisme']); ?>">

        <label>Encadrant externe :</label>
        <input type="text" name="encadrant_ex" value="<?php echo htmlspecialchars($pfe['encadrant_ex']); ?>">

        <label>Email encadrant externe :</label>
        <input type="email" name="email_ex" value="<?php echo htmlspecialchars($pfe['email_ex']); ?>">

        <label>Rapport (PDF facultatif) :</label>
        <input type="file" name="rapport">

        <button type="submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
