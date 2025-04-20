<?php
session_start();
include('../connexion_db.php');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];
$errors = [];
$success = "";


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: enseignant.php");
    exit();
}

$pfe_id = $_GET['id'];


$req_pfe = $conn->query("
    SELECT p.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom
    FROM pfes p 
    JOIN etudiants e ON p.etudiant_id = e.id 
    WHERE p.id = $pfe_id AND p.encadrant_in_id = $enseignant_id
");

if ($req_pfe->num_rows === 0) {
    header("Location: enseignant.php");
    exit();
}

$pfe = $req_pfe->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $titre = trim($_POST['titre']);
    $resume = trim($_POST['resume']);
    $organisme = trim($_POST['organisme']);
    $encadrant_ex = trim($_POST['encadrant_ex']);
    $email_ex = trim($_POST['email_ex']);

    
    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire";
    }

    if (empty($organisme)) {
        $errors[] = "L'organisme est obligatoire";
    }

    if (empty($encadrant_ex)) {
        $errors[] = "L'encadrant externe est obligatoire";
    }

    
    $rapport = $pfe['rapport']; 

    if (isset($_FILES['rapport']) && $_FILES['rapport']['error'] === 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['rapport']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array(strtolower($filetype), $allowed)) {
            $errors[] = "Le format du rapport n'est pas autorisé. Utilisez PDF, DOC ou DOCX.";
        } else {
            
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/rapports_pfe/';

            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            
            $new_filename = uniqid('rapport_') . '.' . $filetype;
            $upload_file = $upload_dir . $new_filename;
            $db_file_path = 'rapports_pfe/' . $new_filename; 

            if (move_uploaded_file($_FILES['rapport']['tmp_name'], $upload_file)) {
                
                if (!empty($pfe['rapport']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']);
                }
                $rapport = $db_file_path;
            } else {
                $errors[] = "Erreur lors du téléchargement du fichier.";
            }
        }
    }

    
    if (empty($errors)) {
        $query = "UPDATE pfes SET 
                  titre = ?, 
                  resume = ?, 
                  organisme = ?, 
                  encadrant_ex = ?, 
                  email_ex = ?, 
                  rapport = ? 
                  WHERE id = ? AND encadrant_in_id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $titre, $resume, $organisme, $encadrant_ex, $email_ex, $rapport, $pfe_id, $enseignant_id);

        if ($stmt->execute()) {
            $success = "Le PFE a été mis à jour avec succès.";
            
            $pfe['titre'] = $titre;
            $pfe['resume'] = $resume;
            $pfe['organisme'] = $organisme;
            $pfe['encadrant_ex'] = $encadrant_ex;
            $pfe['email_ex'] = $email_ex;
            $pfe['rapport'] = $rapport;
        } else {
            $errors[] = "Erreur lors de la mise à jour: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier PFE - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="for" id="login-form">
        <form method="post" enctype="multipart/form-data" class="modifier-pfe-form">
            <h2 class="login_h2">Modifier le PFE</h2>
            <p class="p">Projet de: <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?></p>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div>
                <label>Titre du PFE</label>
                <input class="login_input" type="text" name="titre" value="<?= htmlspecialchars($pfe['titre']) ?>" placeholder="Titre*" required>
            </div>

            <div>
                <label>Résumé</label>
                <textarea class="login_input" name="resume" placeholder="Résumé"><?= htmlspecialchars($pfe['resume']) ?></textarea>
            </div>

            <div>
                <label>Organisme</label>
                <input class="login_input" type="text" name="organisme" value="<?= htmlspecialchars($pfe['organisme']) ?>" placeholder="Organisme*" required>
            </div>

            <div>
                <label>Encadrant externe</label>
                <input class="login_input" type="text" name="encadrant_ex" value="<?= htmlspecialchars($pfe['encadrant_ex']) ?>" placeholder="Encadrant externe*" required>
            </div>

            <div>
                <label>Email encadrant externe</label>
                <input class="login_input" type="email" name="email_ex" value="<?= htmlspecialchars($pfe['email_ex']) ?>" placeholder="Email encadrant externe">
            </div>

            <div>
                <label>Rapport de PFE (PDF, DOC, DOCX)</label>
                <input class="login_input" type="file" name="rapport" accept=".pdf,.doc,.docx">
                
                <?php if (!empty($pfe['rapport'])): ?>
                    <div class="current-file">
                        Fichier actuel: 
                        <a href="/<?= htmlspecialchars($pfe['rapport']) ?>" target="_blank">
                            <?= htmlspecialchars(basename($pfe['rapport'])) ?>
                        </a>
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'])): ?>
                            (<?= round(filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']) / 1024) ?> KB)
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="current-file">Aucun rapport actuellement.</div>
                <?php endif; ?>
                <p class="p">Laissez vide pour conserver le rapport actuel.</p>
            </div>

            <div class="form-actions">
                <button class="login_button" type="submit">Enregistrer</button>
                <a href="consulter_pfe.php?id=<?= $pfe_id ?>" class="login_button cancel">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>