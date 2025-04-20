<?php
include("../../connect_ddb.php");

$output = "";

if (isset($_GET["email"])) {
    $email = htmlspecialchars($_GET["email"]);

    // Vérifier si l'enseignant existe
    $verif_sql = "SELECT id FROM enseignants WHERE email = ?";
    $verif_stmt = $conn->prepare($verif_sql);
    $verif_stmt->bind_param("s", $email);
    $verif_stmt->execute();
    $verif_result = $verif_stmt->get_result();

    if ($verif_result->num_rows === 0) {
        $output = "<p class='error'>Aucun enseignant trouvé avec cet email.</p>";
    } else {
        $row_ens = $verif_result->fetch_assoc();
        $enseignant_id = $row_ens["id"];

        // Requête pour récupérer les PFEs encadrés
        $sql = "SELECT 
                    e.nom AS nom_etudiant, 
                    e.prenom AS prenom_etudiant, 
                    p.titre, 
                    p.organisme, 
                    p.resume, 
                    p.rapport
                FROM pfes p
                INNER JOIN etudiants e ON p.etudiant_id = e.id
                WHERE p.encadrant_in_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $enseignant_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $output = "<p class='warning'>Aucun PFE trouvé pour cet enseignant ($email).</p>";
        } else {
            $output .= "<h2>PFEs encadrés par l'enseignant ($email)</h2>";
            $output .= "<table>
                        <tr>
                            <th>Nom Étudiant</th>
                            <th>Prénom Étudiant</th>
                            <th>Titre</th>
                            <th>Organisme</th>
                            <th>Résumé</th>
                            <th>Rapport</th>
                        </tr>";

            while ($row = $result->fetch_assoc()) {
                $rapport_link = $row["rapport"]
                    ? "<a href='" . htmlspecialchars($row["rapport"]) . "' target='_blank'>Télécharger</a>"
                    : "Aucun fichier";

                $output .= "<tr>
                                <td>" . htmlspecialchars($row["nom_etudiant"]) . "</td>
                                <td>" . htmlspecialchars($row["prenom_etudiant"]) . "</td>
                                <td>" . htmlspecialchars($row["titre"]) . "</td>
                                <td>" . htmlspecialchars($row["organisme"]) . "</td>
                                <td>" . nl2br(htmlspecialchars($row["resume"])) . "</td>
                                <td>$rapport_link</td>
                            </tr>";
            }

            $output .= "</table>";
        }

        $stmt->close();
    }

    $verif_stmt->close();
} else {
    $output = "<p class='error'>Veuillez fournir un email.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PFEs Encadrés</title>
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

        h2 {
            text-align: center;
            margin-top: 30px;
            color: #003366;
        }

        a {
            display: inline-block;
            margin: 20px 0;
            color: #003366;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .content {
            max-width: 90%;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }

        table th {
            background-color: #003366;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        p {
            font-size: 1.1em;
            text-align: center;
            margin: 20px 0;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .warning {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Résultat de la Recherche</h1>
<div class="content">
    <a href="recherche_enseignant.php"> Retour à la recherche</a>
    <hr>
    <?php echo $output; ?>
</div>
</body>
</html>