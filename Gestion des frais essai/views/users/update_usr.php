<?php
require_once __DIR__ . '/../../pdo/bdd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['id'], $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['role'])) {
        die("<div class='text-red-600'>Erreur : Tous les champs sont requis.</div>");
    }

    $id = $_POST['id'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $roleId = $_POST['role'];

    // Vérification de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<div class='text-red-600'>Erreur : Adresse e-mail invalide.</div>");
    }

    // Mise à jour de l'utilisateur
    $update = $cnx->prepare("UPDATE users SET user_firstname = ?, user_lastname = ?, user_email = ?, id_role = ? WHERE id_user = ?");
    $update->execute([$firstname, $lastname, $email, $roleId, $id]);

    echo '<div class="text-green-600">✅ Utilisateur modifié avec succès !</div>';
} else {
    die("<div class='text-red-600'>Erreur : Aucune donnée reçue.</div>");
}
?>