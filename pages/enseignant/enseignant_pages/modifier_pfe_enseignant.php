<?php
include("../../connect_ddb.php");

$output = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST["email"]);

    // Vérifier si l'enseignant existe en fonction de l'email
    $stmt = $conn->prepare("SELECT id FROM enseignants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $output = "<p class='error'>Aucun enseignant trouvé avec cet email.</p>";
    } else {
        $enseignant = $result->fetch_assoc();
        $enseignant_id = $enseignant["id"];

        // Récupérer les PFEs encadrés par cet enseignant
        $sql = "SELECT p.id, e.nom, e.prenom, p.titre FROM pfes p
                INNER JOIN etudiants e ON p.etudiant_id = e.id
                WHERE p.encadrant_in_id = ?";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("i", $enseignant_id);
        $stmt2->execute();
        $pfes = $stmt2->get_result();

        if ($pfes->num_rows === 0) {
            $output = "<p class='error'>Aucun PFE trouvé pour cet enseignant.</p>";
        } else {
            $output = "<table>
                        <tr>
                            <th>Étudiant</th>
                            <th>Titre</th>
                            <th>Action</th>
                        </tr>";
            while ($row = $pfes->fetch_assoc()) {
                $prenom = htmlspecialchars($row['prenom']);
                $nom = htmlspecialchars($row['nom']);
                $titre = htmlspecialchars($row['titre']);
                $id = (int)$row['id'];

                $output .= "<tr>
                                <td>$prenom $nom</td>
                                <td>$titre</td>
                                <td><a href='modifier_formulaire_pfe.php?pfe_id=$id'>Modifier</a></td>
                            </tr>";
            }
            $output .= "</table>";
        }

        $stmt2->close();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier les PFEs encadrés</title>
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
            margin: 50px auto;
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        label {
            font-weight: bold;
            color: #003366;
            display: block;
            margin-bottom: 8px;
        }

        input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus {
            border-color: #005599;
            outline: none;
        }

        button {
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            display: block;
            margin: 20px auto 0;
            transition: background-color 0.3s ease;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #003366;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>Modifier les PFEs encadrés</h1>

<div class="container">
    <form method="post" action="">
        <label for="email">Votre adresse email :</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Rechercher</button>
    </form>

    <?php echo $output; ?>
</div>

</body>
</html>
