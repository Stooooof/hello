<?php
session_start();
include('../../connexion_db.php');

// Vérification de l'authentification et des permissions
if (!isset($_SESSION['email'])) {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: table_pfes.php');
    exit();
}

$pfe_id = (int)$_GET['id'];

// Vérification des permissions
$user_query = $conn->query("SELECT role, user_id FROM users WHERE email = '{$_SESSION['email']}'");
$user = $user_query->fetch_assoc();

$pfe_query = $conn->query("SELECT etudiant_id, encadrant_in_id, rapport FROM pfes WHERE id = $pfe_id");
$pfe = $pfe_query->fetch_assoc();

if ($user['role'] === 'etudiant' && $pfe['etudiant_id'] != $user['user_id']) {
    die("Accès non autorisé");
}
if ($user['role'] === 'enseignant' && $pfe['encadrant_in_id'] != $user['user_id']) {
    die("Accès non autorisé");
}

// Suppression du fichier si existe
if ($pfe['rapport']) {
    unlink("C:/xampp/htdocs/" . $pfe['rapport']);
}

// Suppression du PFE
$conn->query("DELETE FROM pfes WHERE id = $pfe_id");

$_SESSION['success'] = "PFE supprimé avec succès";
header('Location: table_pfes.php');
exit();
?>
