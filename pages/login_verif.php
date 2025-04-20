<?php
session_start();
include('connexion_db.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérification de l'utilisateur dans la table users
    $checkUser = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($checkUser->num_rows > 0) {
        $user = $checkUser->fetch_assoc();

        // Vérification du mot de passe
        if ($password === $user['password']) {
            // Vérification supplémentaire selon le rôle
            $canLogin = false;
            $errorMessage = '';

            if ($user['role'] === 'admin') {
                $canLogin = true;
            }
            elseif ($user['role'] === 'enseignant') {
                // Utilisation de l'email pour trouver l'enseignant
                $enseignantCheck = $conn->query("SELECT id FROM enseignants WHERE email = '".$user['email']."'");
                if ($enseignantCheck->num_rows > 0) {
                    $canLogin = true;
                    $_SESSION['enseignant_id'] = $enseignantCheck->fetch_assoc()['id'];
                } else {
                    $errorMessage = 'VOTRE PROFIL ENSEIGNANT N\'EST PAS COMPLÉTÉ. CONTACTEZ L\'ADMINISTRATION.';
                }
            }
            elseif ($user['role'] === 'etudiant') {
                // Utilisation de l'email pour trouver l'étudiant
                $etudiantCheck = $conn->query("SELECT id FROM etudiants WHERE email = '".$user['email']."'");
                if ($etudiantCheck->num_rows > 0) {
                    $canLogin = true;
                    $_SESSION['etudiant_id'] = $etudiantCheck->fetch_assoc()['id'];
                } else {
                    $errorMessage = 'VOTRE PROFIL ÉTUDIANT N\'EST PAS COMPLÉTÉ. CONTACTEZ L\'ADMINISTRATION.';
                }
            }

            if ($canLogin) {
                // Stockage des informations de session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirection selon le rôle
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