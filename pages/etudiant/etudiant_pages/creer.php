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

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="file"],
        textarea {
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

        input:focus,
        textarea:focus {
            border-color: #005599;
            outline: none;
        }

        textarea {
            resize: vertical;
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
            margin: 30px auto 0;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #005599;
        }

        .message {
            text-align: center;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 6px;
            font-size: 15px;
            line-height: 1.5;
        }

        .message.success {
            background-color: #e6f7ec;
            color: #237c3b;
            border: 1px solid #b6e6c7;
        }

        .message.error {
            background-color: #fdecea;
            color: #c9302c;
            border: 1px solid #f5c6cb;
        }

        a {
            color: #003366;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Créer un PFE</h1>

    <?= $message ?>

    <form method="post" action="" enctype="multipart/form-data">
        <label>ID Étudiant :</label>
        <input type="number" name="etudiant_id" required>

        <label>Titre :</label>
        <input type="text" name="titre" required>

        <label>Résumé :</label>
        <textarea name="resume" rows="5" required></textarea>

        <label>Organisme :</label>
        <input type="text" name="organisme">

        <label>Encadrant Externe :</label>
        <input type="text" name="encadrant_ex">

        <label>Email Encadrant Externe :</label>
        <input type="email" name="email_ex">

        <label>ID Encadrant Interne :</label>
        <input type="number" name="encadrant_in_id">

        <label>Rapport (PDF) :</label>
        <input type="file" name="rapport" accept="application/pdf" required>

        <button type="submit">Créer le PFE</button>
    </form>
</div>

</body>
</html>