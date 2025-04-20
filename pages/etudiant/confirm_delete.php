<?php
session_start();
include('../connexion_db.php');


if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: etudiant.php');
    exit();
}
$pfe_id = (int)$_GET['id'];

$email = $_SESSION['email'];
$etudiant_query = $conn->query("SELECT id FROM etudiants WHERE email = '$email'");
if ($etudiant_query->num_rows === 0) {
    die("Profil étudiant non trouvé");
}
$etudiant = $etudiant_query->fetch_assoc();
$etudiant_id = $etudiant['id'];


$pfe_query = $conn->prepare("SELECT id, titre FROM pfes WHERE id = ? AND etudiant_id = ?");
$pfe_query->bind_param("ii", $pfe_id, $etudiant_id);
$pfe_query->execute();
$pfe = $pfe_query->get_result()->fetch_assoc();

if (!$pfe) {
    $_SESSION['error'] = "PFE non trouvé ou accès non autorisé";
    header('Location: etudiant.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de suppression</title>
    <link rel="stylesheet" href="../mystyles.css">
</head>
<body class="login_body">
<div class="Con">
    <div class="confirmation-box">
        <h2 class="login_h2">Confirmer la suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer le PFE : <strong><?= htmlspecialchars($pfe['titre']) ?></strong> ?</p>
        <p>Cette action est irréversible.</p>
        
        <div class="action-buttons">
            <a href="delete_pfe.php?id=<?= $pfe_id ?>" class="btn btn-danger">Confirmer la suppression</a>
            <a href="etudiant.php" class="btn btn-primary">Annuler</a>
        </div>
    </div>
</div>
</body>
</html>