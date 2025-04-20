
<?php
session_start();

include('../connexion_db.php');
if(!isset($_SESSION['email'])){
    header('location: ../../index.php');
    exit();
}

$nb_etudiants = $conn->query("SELECT COUNT(*) as total FROM etudiants")->fetch_assoc()['total'];
$nb_enseignants = $conn->query("SELECT COUNT(*) as total FROM enseignants")->fetch_assoc()['total'];
$nb_pfes = $conn->query("SELECT COUNT(*) as total FROM pfes")->fetch_assoc()['total'];

$nom_affiche = isset($_SESSION['prenom']) ? htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) : htmlspecialchars($_SESSION['email']);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
    <link rel="icon" href="../../img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="../mystyles.css">
</head>


<body>
<div class="content" >

    <?php
    include("admin_header.php")
    ?>

    <main>
        <div class="main-content">
            <div>
                <h1>Bienvenue, <span><?= $nom_affiche ?></span></h1>
                <p>Ceci est votre espace <strong>administrateur</strong>.</p>

                <div class="stats">
                    <div class="stat">👨‍🎓 Étudiants : <strong><?= $nb_etudiants ?></strong></div>
                    <div class="stat">👩‍🏫 Enseignants : <strong><?= $nb_enseignants ?></strong></div>
                    <div class="stat">📂 PFEs : <strong><?= $nb_pfes ?></strong></div>
                </div>
            </div>
        </div>

    </main>
</div>

<?php
include("../footer.php")
?>

</body>

</html>
