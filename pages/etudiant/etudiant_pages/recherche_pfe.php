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

        h1 {
            background-color: #003366;
            color: white;
            padding: 20px;
            margin: 0;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        label {
            font-weight: bold;
            color: #003366;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #005599;
            outline: none;
        }

        button[type="submit"] {
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

        button[type="submit"]:hover {
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
    <div class="container">
        <h1>Rechercher un PFE</h1>

        <form method="get" action="resultat_pfe.php">
            <label>Nom :</label>
            <input type="text" name="nom" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" required>

            <button type="submit">Rechercher</button>
        </form>
    </div>
</body>
</html>
