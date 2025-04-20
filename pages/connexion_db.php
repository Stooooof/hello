<?php
$host = "localhost" ;
$username = "root" ;
$password = "" ;
$baseDD = "projet" ;


$conn = mysqli_connect($host, $username, $password, $baseDD);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

?>