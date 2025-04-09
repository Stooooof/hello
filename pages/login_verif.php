<?php 

$a = $_POST["email"] ;
$b = $_POST["password"] ;

session_start() ;
$_SESSION["email"] = $a ;
$_SESSION["password"] = $b ;

?>