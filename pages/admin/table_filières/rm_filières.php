<?php
session_start();
include('../../connexion_db.php');

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error'] = "ID de filière invalide";
    header('Location: table_filières.php');
    exit();
}

$id = (int)$_GET['id'];

$check_filiere = $conn->prepare("SELECT nom FROM filieres WHERE id = ?");
$check_filiere->bind_param("i", $id);
$check_filiere->execute();
$filiere = $check_filiere->get_result()->fetch_assoc();

if (!$filiere) {
    $_SESSION['error'] = "Filière introuvable";
    header('Location: table_filières.php');
    exit();
}

$constraints = [
    'etudiants' => [
        'sql' => "SELECT COUNT(*) FROM etudiants WHERE fil_id = ?",
        'message' => "%d étudiant(s) inscrit(s) dans cette filière"
    ],
    'pfes' => [
        'sql' => "SELECT COUNT(*) FROM pfes WHERE etudiant_id IN (SELECT id FROM etudiants WHERE fil_id = ?)",
        'message' => "%d PFE associé(s) à cette filière"
    ]
];

$blockers = [];
foreach ($constraints as $key => $constraint) {
    $stmt = $conn->prepare($constraint['sql']);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];

    if ($count > 0) {
        $blockers[] = sprintf($constraint['message'], $count);
    }
}

if (!empty($blockers)) {
    $_SESSION['error'] = "Impossible de supprimer '".htmlspecialchars($filiere['nom'])."' :<br>- " . implode("<br>- ", $blockers);
    header('Location: table_filières.php');
    exit();
}

try {
    $conn->begin_transaction();

    $delete_stmt = $conn->prepare("DELETE FROM filieres WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "Filière '".htmlspecialchars($filiere['nom'])."' supprimée avec succès";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Erreur technique : " . $e->getMessage();
}

header('Location: table_filières.php');
exit();
?>
