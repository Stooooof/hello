<?php
include("../../connexion_db.php");

$output = "";

if (isset($_GET["nom"]) && isset($_GET["prenom"])) {
    $nom = htmlspecialchars($_GET["nom"]);
    $prenom = htmlspecialchars($_GET["prenom"]);

    $sql = "SELECT e.id, e.nom, e.prenom, p.titre, p.resume, p.organisme, p.encadrant_ex, p.email_ex, p.rapport
            FROM etudiants e
            LEFT JOIN pfes p ON e.id = p.etudiant_id
            WHERE e.nom = ? AND e.prenom = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nom, $prenom);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $output = "<p class='error'>Aucun étudiant trouvé avec ce nom et prénom.</p>";
    } else {
        $row = $result->fetch_assoc();
        if ($row["titre"] === null) {
            $output = "<p class='warning'>Aucun PFE n'est enregistré pour cet étudiant.</p>";
        } else {
            $output .= "<h2>PFE de {$row['prenom']} {$row['nom']}</h2>";
            $output .= "<table>
                            <tr><th>Titre</th><td>" . htmlspecialchars($row["titre"]) . "</td></tr>
                            <tr><th>Résumé</th><td>" . nl2br(htmlspecialchars($row["resume"])) . "</td></tr>
                            <tr><th>Organisme</th><td>" . htmlspecialchars($row["organisme"]) . "</td></tr>
                            <tr><th>Encadrant Externe</th><td>" . htmlspecialchars($row["encadrant_ex"]) . "</td></tr>
                            <tr><th>Email Encadrant</th><td>" . htmlspecialchars($row["email_ex"]) . "</td></tr>
                            <tr><th>Rapport</th><td>" . htmlspecialchars($row["rapport"]) . "</td></tr>
                       </table>";
        }
    }

    $stmt->close();
} else {
    $output = "<p class='error'>Veuillez fournir un nom et un prénom.</p>";
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
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            background-color: #003366;
            color: white;
            padding: 20px;
            margin: 0 0 20px 0;
            text-align: center;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            color: #003366;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            transition: 0.3s;
        }

        a:hover {
            color: #005599;
            text-decoration: underline;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .warning {
            color: #ff6600;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            font-size: 15px;
        }

        th {
            background-color: #d9e6f2;
            color: #003366;
            font-weight: bold;
        }

        td {
            color: #333;
            background-color: #f9f9f9;
        }

        #resultat {
            margin-top: 20px;
        }

        @media screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }

            table, th, td {
                font-size: 14px;
            }

            th, td {
                padding: 10px 12px;
            }
        }
    </style>

</head>
<body>
<div class="container">
    <h1>Résultat de la Recherche</h1>

    <div style="text-align: center;">
        <a href="recherche_pfe.php" class="btn-retour"> Retour à la recherche</a>

    </div>

    <div id="resultat">
        <?php echo $output; ?>
    </div>
</div>
</body>
</html>
