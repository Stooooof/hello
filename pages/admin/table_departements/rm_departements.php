<?php
session_start();
include('../../connexion_db.php');

// 1. Vérification de l'ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error'] = "ID de département invalide";
    header('Location: table_departements.php');
    exit();
}

$id = (int)$_GET['id'];

// 2. Vérifier que le département existe
$check_dept = $conn->prepare("SELECT nom FROM departements WHERE id = ?");
$check_dept->bind_param("i", $id);
$check_dept->execute();
$dept = $check_dept->get_result()->fetch_assoc();

if (!$dept) {
    $_SESSION['error'] = "Département introuvable";
    header('Location: table_departements.php');
    exit();
}

// 3. Vérifier les contraintes avant suppression
$constraints = [
    'filieres' => [
        'sql' => "SELECT COUNT(*) FROM filieres WHERE dept_id = ?",
        'message' => "Ce département contient %d filière(s)"
    ],
    'enseignants' => [
        'sql' => "SELECT COUNT(*) FROM enseignants WHERE dept_id = ?",
        'message' => "%d enseignant(s) rattaché(s) à ce département"
    ],
    'chefs' => [
        'sql' => "SELECT COUNT(*) FROM departements WHERE chef_id IN (SELECT id FROM enseignants WHERE dept_id = ?)",
        'message' => "%d enseignant(s) de ce département sont chef(s) d'autre(s) département(s)"
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

// 4. Si contraintes trouvées
if (!empty($blockers)) {
    $_SESSION['error'] = "Impossible de supprimer '".htmlspecialchars($dept['nom'])."' :<br>- " . implode("<br>- ", $blockers);
    header('Location: table_departements.php');
    exit();
}

// 5. Suppression si aucune contrainte
try {
    $conn->begin_transaction();

    // D'abord retirer les éventuelles références comme chef
    $update_chefs = $conn->prepare("UPDATE departements SET chef_id = NULL WHERE chef_id IN (SELECT id FROM enseignants WHERE dept_id = ?)");
    $update_chefs->bind_param("i", $id);
    $update_chefs->execute();

    // Ensuite supprimer le département
    $delete_stmt = $conn->prepare("DELETE FROM departements WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "Département '".htmlspecialchars($dept['nom'])."' supprimé avec succès";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Erreur technique : " . $e->getMessage();
}

// 6. Redirection
header('Location: table_departements.php');
exit();
?>
