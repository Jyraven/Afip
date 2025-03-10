<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('pdo/bdd.php');

function isUserLoggedIn()
{
    return isset($_SESSION['user']);
}
// Premier affichage
if (!isUserLoggedIn()) {
    // Si l'utilisateur n'est pas connecté, afficher le formulaire de connexion
    require_once('Auth/login.php');
    exit();
}
switch ($_GET['action'] ?? "") {
    case 'delete':
        echo '<h1>Suppression</h1>';
        require_once('views/users/delete_usr.php');
        require_once('views/users/gestion_usr.php');
        break;
    case 'edit':
        echo '<h1>Modifier un utilisateur</h1>';
        require_once('views/users/edit_usr.php');
        break;
    case 'update':
        echo '<h1>Utilisateur modifié</h1>';
        require_once('views/users/update_usr.php');
        break;
    case 'login':
        require_once('Auth/login.php');
        break;
    case 'Administrateur':
        if ($_SESSION['user']['role'] == 'Administrateur') {
            header("Location: vues/admin.php");
            exit();
        }
        break;
    case 'Comptable':
        if ($_SESSION['user']['role'] == 'Comptable') {
            header("Location: vues/comptable.php");
            exit();
        }
        break;
    case 'Visiteur':
        if ($_SESSION['user']['role'] == 'Visiteur') {
            header("Location: vues/visiteur.php");
            exit();
        }
        
    default:
        echo '<h1>Accueil</h1>';
        require_once('views/users/users.php');
        require_once('views/users/gestion_usr.php');
        break;
}