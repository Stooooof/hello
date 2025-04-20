<?php
session_start();
include('../connexion_db.php');

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../login.php");
    exit();
}

$enseignant_id = $_SESSION['enseignant_id'];

// Vérification de l'ID du PFE
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: enseignant.php");
    exit();
}

$pfe_id = $_GET['id'];

// Récupération des détails du PFE avec vérification que l'enseignant est bien l'encadrant
$req_pfe = $conn->query("
    SELECT p.*, 
           e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.email AS etudiant_email, e.matricule,
           f.nom AS filiere_nom, 
           d.nom AS departement_nom
    FROM pfes p 
    JOIN etudiants e ON p.etudiant_id = e.id 
    JOIN filieres f ON e.fil_id = f.id
    JOIN departements d ON f.dept_id = d.id
    WHERE p.id = $pfe_id AND p.encadrant_in_id = $enseignant_id
");

if ($req_pfe->num_rows === 0) {
    header("Location: enseignant.php");
    exit();
}

$pfe = $req_pfe->fetch_assoc();

// Téléchargement du fichier
if (isset($_GET['download']) && $pfe['rapport']) {
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'];

    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        $_SESSION['error'] = "Le fichier n'existe pas sur le serveur";
        header("Location: consulter_pfe.php?id=$pfe_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consulter PFE - SMART-PFE</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body>
<?php include('enseignant_header.php'); ?>

<div class="container">
    <h1>Détails du PFE</h1>

    <div class="back-link">
        <a href="enseignant.php">← Retour à la liste</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="pfe-details">
        <h2><?= htmlspecialchars($pfe['titre']) ?></h2>

        <div class="info-grid">
            <div class="info-group">
                <h3>Informations sur l'étudiant</h3>
                <p><strong>Nom:</strong> <?= htmlspecialchars($pfe['etudiant_prenom'] . ' ' . $pfe['etudiant_nom']) ?></p>
                <p><strong>Matricule:</strong> <?= htmlspecialchars($pfe['matricule']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($pfe['etudiant_email']) ?></p>
                <p><strong>Filière:</strong> <?= htmlspecialchars($pfe['filiere_nom']) ?></p>
                <p><strong>Département:</strong> <?= htmlspecialchars($pfe['departement_nom']) ?></p>
            </div>

            <div class="info-group">
                <h3>Informations sur le PFE</h3>
                <p><strong>Organisme:</strong> <?= htmlspecialchars($pfe['organisme']) ?></p>
                <p><strong>Encadrant externe:</strong> <?= htmlspecialchars($pfe['encadrant_ex']) ?></p>
                <p><strong>Email encadrant externe:</strong> <?= htmlspecialchars($pfe['email_ex']) ?></p>

                <?php if (!empty($pfe['rapport'])): ?>
                    <p>
                        <strong>Rapport:</strong>
                        <a href="consulter_pfe.php?id=<?= $pfe_id ?>&download=1" class="btn btn-download">Télécharger</a>
                        <span>(Taille: <?= round(filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport']) / 1024) ?> KB)</span>
                    </p>
                <?php else: ?>
                    <p><strong>Rapport:</strong> Non disponible</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="resume-section">
            <h3>Résumé du projet</h3>
            <div class="resume-content">
                <?= nl2br(htmlspecialchars($pfe['resume'])) ?>
            </div>
        </div>

        <div class="actions">
            <a href="modifier_pfe.php?id=<?= $pfe['id'] ?>" class="btn btn-primary">Modifier ce PFE</a>
        </div>
    </div>
</div>

<?php include('enseignant_footer.php'); ?>
</body>
</html>
