<?php
include("connexion_db.php");

$resultats = [];

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $motCle = htmlspecialchars($_GET['q']);
    $motCleLike = "%$motCle%";

    $sql = "SELECT * FROM pfes WHERE titre LIKE ? OR resume LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $motCleLike, $motCleLike);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $resultats[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de PFEs</title>
    <link rel="icon" href="../img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="mystyles.css">
</head>
<body class="table_body">
    <div class="nav_links">
        <a href="admin/admin.php" class="p"><button class="login_button"> ⬅️ Accueil </button></a>
    </div>
    <h2 class="login_h2">Recherche de PFEs</h2>
    <form method="get" action="">
        <div class="search-container">
            <input class="search-input" type="text" name="q" placeholder="Entrez un mot-clé..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <div class="nav_links">
                <br>
                <button type="submit" class="login_button">Chercher</button>
            </div>
        </div>
    </form>

    <?php if (!empty($_GET['q'])): ?>
        <h2 class="p">Résultats pour "<?= htmlspecialchars($_GET['q']) ?>" :</h2>
        
        <?php if (!empty($resultats)): ?>
            <div class="search-table-container">
                <table class="crud_table">
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Résumé</th>
                        <th>Organisme</th>
                        <th>Rapport</th>
                    </tr>
                    </thead>
                    <tbody class="p">
                    <?php foreach ($resultats as $pfe): ?>
                        <tr>
                            <td><?= htmlspecialchars($pfe['titre']) ?></td>
                            <td><?= nl2br(htmlspecialchars(substr($pfe['resume'], 0, 150) . (strlen($pfe['resume']) > 150 ? '...' : ''))) ?></td>
                            <td><?= htmlspecialchars($pfe['organisme']) ?></td>
                            <td>
                                <?php 
                                
                                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $pfe['rapport'];
                                
                                $relativePath = $pfe['rapport'];
                                
                                if (!empty($pfe['rapport']) && file_exists($filePath)): ?>
                                    <a href="/<?= htmlspecialchars($relativePath) ?>" class="pfe-link" download>
                                        Télécharger
                                    </a>
                                <?php else: ?>
                                    <span class="no-file">Non disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p class="p">Aucun PFE trouvé.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
