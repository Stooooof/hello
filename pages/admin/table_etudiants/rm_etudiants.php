<?php
include('../../connexion_db.php');

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = (int)$_GET['id'];

    $check = $conn->prepare("SELECT id FROM etudiants WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();

    if ($check->get_result()->num_rows === 1) {
        $sql = "DELETE FROM etudiants WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Étudiant supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }
    }
}

header('Location: table_etudiants.php');
exit();
