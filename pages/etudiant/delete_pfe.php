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


$pfe_query = $conn->prepare("SELECT rapport FROM pfes WHERE id = ? AND etudiant_id = ?");
$pfe_query->bind_param("ii", $pfe_id, $etudiant_id);
$pfe_query->execute();
$pfe = $pfe_query->get_result()->fetch_assoc();

if ($pfe) {
    
    if (!empty($pfe['rapport']) && file_exists("../" . $pfe['rapport'])) {
        unlink("../" . $pfe['rapport']);
    }
    
   
    $delete_query = $conn->prepare("DELETE FROM pfes WHERE id = ?");
    $delete_query->bind_param("i", $pfe_id);
    $delete_query->execute();
    
    $_SESSION['success'] = "PFE supprimé avec succès";
} else {
    $_SESSION['error'] = "PFE non trouvé ou accès non autorisé";
}

header('Location: etudiant.php');
exit();