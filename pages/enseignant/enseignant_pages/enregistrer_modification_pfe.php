<?php
include("../../connect_ddb.php");

$message = "";
$styleClass = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pfe_id = intval($_POST["pfe_id"]);
    $titre = htmlspecialchars($_POST["titre"]);
    $resume = htmlspecialchars($_POST["resume"]);
    $organisme = htmlspecialchars($_POST["organisme"]);
    $encadrant_ex = htmlspecialchars($_POST["encadrant_ex"]);
    $email_ex = htmlspecialchars($_POST["email_ex"]);

    // Préparer la requête de base
    $sql = "UPDATE pfes SET titre=?, resume=?, organisme=?, encadrant_ex=?, email_ex=?";

    // Gestion de l’upload du fichier PDF
    if (isset($_FILES["rapport"]) && $_FILES["rapport"]["error"] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES["rapport"]["tmp_name"];
        $fileName = $_FILES["rapport"]["name"];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt === "pdf") {
            $newName = uniqid("rapport_") . ".pdf";
            $uploadDir = "../../uploads/";
            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $sql .= ", rapport=?";
                $params = [$titre, $resume, $organisme, $encadrant_ex, $email_ex, $newName, $pfe_id];
                $types = "ssssssi";
            } else {
                $message = "Échec de l'upload du fichier PDF.";
                $styleClass = "error";
            }
        } else {
            $message = "Le fichier doit être un PDF.";
            $styleClass = "error";
        }
    } else {
        $params = [$titre, $resume, $organisme, $encadrant_ex, $email_ex, $pfe_id];
        $types = "sssssi";
    }

    // Exécuter la requête si pas d’erreur d’upload
    if ($message === "") {
        $sql .= " WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = "Le PFE a été modifié avec succès.";
            $styleClass = "success";
        } else {
            $message = "Erreur lors de la mise à jour du PFE.";
            $styleClass = "error";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrement de la modification</title>
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
            text-align: center;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
        }

        a:hover {
            background-color: #005599;
        }
    </style>
</head>
<body>
<h1>Modification du PFE</h1>
<div class="container">
    <p class="<?php echo $styleClass; ?>"><?php echo $message; ?></p>
    <a href="modifier_pfe_enseignant.php">Retour à la liste</a>
</div>
</body>
</html>
