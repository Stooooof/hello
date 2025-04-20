<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

// Récupérer les informations de l'étudiant
$email = $_SESSION['email'];
$user_query = $conn->query("SELECT u.*, e.nom, e.prenom, e.matricule 
                          FROM users u 
                          JOIN etudiants e ON u.user_id = e.id 
                          WHERE u.email = '$email' AND u.role = 'etudiant'");

if ($user_query->num_rows === 0) {
    header('Location: ../login.php');
    exit();
}

$user = $user_query->fetch_assoc();
$etudiant_id = $user['user_id'];

// Récupérer les PFEs de l'étudiant
$pfes = $conn->query("SELECT * FROM pfes WHERE etudiant_id = $etudiant_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiant - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .welcome {
            text-align: center;
            margin-bottom: 30px;
        }
        .pfe-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .pfe-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .pfe-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        .add-pfe {
            display: block;
            text-align: center;
            margin: 20px 0;
        }
        .logout {
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="login_body">
<div class="Con">
    <div class="dashboard">
        <div class="logout">
            <a href="../logout.php" class="btn">Déconnexion</a>
        </div>

        <div class="welcome">
            <h2 class="login_h2">Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
            <p>Matricule: <?= htmlspecialchars($user['matricule']) ?></p>
        </div>

        <div class="add-pfe">
            <a href="add_pfe.php" class="btn btn-success">+ Ajouter un PFE</a>
        </div>

        <div class="pfe-list">
            <?php if ($pfes->num_rows > 0): ?>
                <?php while ($pfe = $pfes->fetch_assoc()): ?>
                    <div class="pfe-card">
                        <h3><?= htmlspecialchars($pfe['titre']) ?></h3>
                        <p><strong>Organisme:</strong> <?= htmlspecialchars($pfe['organisme']) ?></p>
                        <p><strong>Statut:</strong> <?= $pfe['rapport'] ? 'Complet' : 'En cours' ?></p>

                        <div class="pfe-actions">
                            <a href="view_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Voir</a>
                            <a href="edit_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Modifier</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Aucun PFE enregistré pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
