<!-- recherche_enseignant.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rechercher les PFEs encadrés</title>
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

        form {
            background-color: white;
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            width: 100%;
        }

        button:hover {
            background-color: #0055a5;
        }
    </style>
</head>
<body>
<h1>Consulter les PFEs que vous encadrez</h1>

<form method="get" action="pfes_encadres.php">
    <label for="email">Votre adresse email :</label>
    <input type="email" name="email" id="email" required>
    <button type="submit">Rechercher</button>
</form>
</body>
</html>