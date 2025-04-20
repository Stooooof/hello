<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SMART-PFE</title>
    <link rel="icon" href="img/icon.png" type="image/x-icon">
    <link rel="stylesheet" href="pages/mystyles.css">
</head>

<body>

    <?php
        include("pages/header.php")
    ?>

    <div class="content">
        <main>
            
            <section class="hero">
                <h1>Bienvenue sur SMART-PFE</h1>
                <p>La plateforme intelligente de gestion des Projets de Fin d'Études pour étudiants, enseignants et administrateurs.</p>
                <div class="hero-buttons">
                    <a href="pages/login.php" class="hero-btn primary">Se connecter</a>
                    <a href="pages/register.php" class="hero-btn secondary">Créer un compte</a>
                </div>
            </section>

            
            <section class="features">
                <h2>Fonctionnalités clés</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Étudiants</h3>
                        <p>Soumettez facilement votre rapport PFE, suivez son statut et échangez avec vos encadrants.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Enseignants</h3>
                        <p>Gérez les PFEs que vous encadrez, validez les rapports et suivez l'évolution de vos étudiants.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Administrateurs</h3>
                        <p>Contrôlez les comptes utilisateurs et accédez à la base complète des projets déposés.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <br>
    <br>
    <br>

    <footer>
    <?php
        include("pages/footer.php")
    ?>
    </footer>

</body>

</html>