<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('includes/bdd.php');

function isUserLoggedIn()
{
    return isset($_SESSION['user']);
}
// Premier affichage
if (!isUserLoggedIn()) {
    // Si l'utilisateur n'est pas connecté, afficher le formulaire de connexion
    require_once('includes/login.php');
    exit();
}
switch ($_GET['action'] ?? "") {
    case 'delete':
        echo '<h1>Suppression</h1>';
        require_once('includes/delete.php');
        require_once('includes/select.php');
        break;
    case 'create':
        echo '<h1>Création d\'un utilisateur</h1>';
        require_once('includes/add_users.php');
        require_once('includes/insert.php');
        require_once('includes/select.php');
        break;
    case 'edit':
        echo '<h1>Modifier un utilisateur</h1>';
        require_once('includes/edit.php');
        break;
    case 'update':
        echo '<h1>Utilisateur modifié</h1>';
        require_once('includes/update.php');
        break;
    case 'login':
        require_once('includes/login.php');
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
        break;
    default:
        echo '<h1>Accueil</h1>';
        require_once('includes/add_users.php');
        require_once('includes/select.php');
        break;
}