<?php
session_start();
if(!isset($_SESSION['email'])){
    header('location: ../../index.php');
    exit();
}

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
        <div class="line"></div>

        <section class="projects">
            <h1>Welcome, <span><?php echo htmlspecialchars($_SESSION['email']); ?></span></h1>
            <p>This is an <span>admin</span> page</p>





        </section>

    </main>
</div>

<?php
include("../footer.php")
?>

</body>

</html>
