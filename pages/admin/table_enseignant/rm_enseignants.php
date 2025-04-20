<?php
include('../../connexion_db.php');

$id = intval($_GET['id']);

$check = $conn->query("SELECT id FROM departements WHERE chef_id = $id");
if ($check->num_rows > 0) {
    $_SESSION['error'] = "Impossible de supprimer : cet enseignant est chef de département";
} else {
    $sql = "DELETE FROM enseignants WHERE id = $id";
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Enseignant supprimé avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression";
    }
}

header('Location: table_enseignants.php');
exit();
