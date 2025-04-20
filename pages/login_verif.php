<?php
session_start();
include('connexion_db.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $checkUser = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($checkUser->num_rows > 0) {
        $user = $checkUser->fetch_assoc();

        
        if ($password === $user['password']) {
            
            $canLogin = false;
            $errorMessage = '';

            if ($user['role'] === 'admin') {
                $canLogin = true;
            }
            elseif ($user['role'] === 'enseignant') {
               
                $enseignantCheck = $conn->query("SELECT id FROM enseignants WHERE email = '".$user['email']."'");
                if ($enseignantCheck->num_rows > 0) {
                    $canLogin = true;
                    $_SESSION['enseignant_id'] = $enseignantCheck->fetch_assoc()['id'];
                } else {
                    $errorMessage = 'VOTRE PROFIL ENSEIGNANT N\'EST PAS COMPLÉTÉ. CONTACTEZ L\'ADMINISTRATION.';
                }
            }
            elseif ($user['role'] === 'etudiant') {
               
                $etudiantCheck = $conn->query("SELECT id FROM etudiants WHERE email = '".$user['email']."'");
                if ($etudiantCheck->num_rows > 0) {
                    $canLogin = true;
                    $_SESSION['etudiant_id'] = $etudiantCheck->fetch_assoc()['id'];
                } else {
                    $errorMessage = 'VOTRE PROFIL ÉTUDIANT N\'EST PAS COMPLÉTÉ. CONTACTEZ L\'ADMINISTRATION.';
                }
            }

            if ($canLogin) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                
                if($user['role'] === 'admin') {
                    header("Location: admin/admin.php");
                } elseif($user['role'] === 'enseignant') {
                    header("Location: enseignant/enseignant.php");
                } elseif($user['role'] === 'etudiant') {
                    header("Location: etudiant/etudiant.php");
                }
                exit();
            } else {
                $_SESSION["login_error"] = $errorMessage;
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION["login_error"] = 'MOT DE PASSE INCORRECT';
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION["login_error"] = 'EMAIL INTROUVABLE';
        header("Location: login.php");
        exit();
    }
}
?>