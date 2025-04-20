<?php
include('../../connexion_db.php');

// Vérifier que l'ID est présent et numérique
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Vérifier d'abord si l'étudiant existe
    $check = $conn->prepare("SELECT id FROM etudiants WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();

    if ($check->get_result()->num_rows === 1) {
        // Supprimer l'étudiant
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

// Redirection vers la liste
header('Location: table_etudiants.php');
exit();
