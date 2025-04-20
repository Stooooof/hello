<?php
include("../../connexion_db.php");

$pfeTrouve = false;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["modifier"])) {
        $etudiant_id = intval($_POST["etudiant_id"]);
        $titre = htmlspecialchars($_POST["titre"]);
        $resume = htmlspecialchars($_POST["resume"]);
        $organisme = htmlspecialchars($_POST["organisme"]);
        $encadrant_ex = htmlspecialchars($_POST["encadrant_ex"]);
        $email_ex = htmlspecialchars($_POST["email_ex"]);
        $rapport = htmlspecialchars($_POST["rapport"]);

        $sql = "UPDATE pfes SET titre = ?, resume = ?, organisme = ?, encadrant_ex = ?, email_ex = ?, rapport = ?
                WHERE etudiant_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $rapport, $etudiant_id);

        if ($stmt->execute()) {
            $message = "<div class='message success'>PFE mis à jour avec succès !</div>";
        } else {
            $message = "<div class='message error'>Erreur lors de la mise à jour : " . $stmt->error . "</div>";
        }
        $stmt->close();
    }

    elseif (isset($_POST["nom"], $_POST["prenom"])) {
        $nom = htmlspecialchars($_POST["nom"]);
        $prenom = htmlspecialchars($_POST["prenom"]);

        $sql = "SELECT e.id AS etudiant_id, e.nom, e.prenom, p.titre, p.resume, p.organisme, p.encadrant_ex, p.email_ex, p.rapport
                FROM etudiants e
                LEFT JOIN pfes p ON e.id = p.etudiant_id
                WHERE e.nom = ? AND e.prenom = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nom, $prenom);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row["titre"] !== null) {
                $pfeTrouve = true;
            } else {
                $message = "<div class='message warning'>Aucun PFE trouvé pour cet étudiant.</div>";
            }
        } else {
            $message = "<div class='message error'>Aucun étudiant trouvé avec ce nom et prénom.</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
    <link rel="icon" href="../../../img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../../mystyles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f6fc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            background-color: #003366;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            margin: -40px -40px 30px -40px;
            font-size: 24px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #003366;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
            background-color: #f9f9f9;
            transition: border 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #003366;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        button[type="submit"] {
            background-color: #003366;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin: auto;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #005599;
        }

        .message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 15px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
    </style>

</head>
<body>
<div class="container">
    <h1>Modifier un PFE</h1>

    <?php echo $message; ?>

    <?php if (!$pfeTrouve): ?>
        <form method="post" action="">
            <label>Nom :</label>
            <input type="text" name="nom" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" required>

            <button type="submit">Chercher mon PFE</button>
        </form>
    <?php else: ?>
        <form method="post" action="">
            <input type="hidden" name="etudiant_id" value="<?php echo $row['etudiant_id']; ?>">
            <input type="hidden" name="modifier" value="1">

            <label>Titre :</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($row['titre']); ?>" required>

            <label>Résumé :</label>
            <textarea name="resume" rows="5" required><?php echo htmlspecialchars($row['resume']); ?></textarea>

            <label>Organisme :</label>
            <input type="text" name="organisme" value="<?php echo htmlspecialchars($row['organisme']); ?>">

            <label>Encadrant Externe :</label>
            <input type="text" name="encadrant_ex" value="<?php echo htmlspecialchars($row['encadrant_ex']); ?>">

            <label>Email Encadrant :</label>
            <input type="email" name="email_ex" value="<?php echo htmlspecialchars($row['email_ex']); ?>">

            <label>Rapport (URL ou nom de fichier) :</label>
            <input type="text" name="rapport" value="<?php echo htmlspecialchars($row['rapport']); ?>">

            <button type="submit">Mettre à jour le PFE</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
