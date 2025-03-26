<?php
require_once __DIR__ . '/../../pdo/bdd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['password'], $_POST['role'])) {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont requis."]);
        exit;
    }

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $roleId = $_POST['role'];

    // Vérification de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Adresse e-mail invalide."]);
        exit;
    }

    // Vérifier si l'email existe déjà
    $checkEmail = $cnx->prepare("SELECT COUNT(*) FROM users WHERE user_email = ?");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetchColumn() > 0) {
        echo json_encode(["status" => "error", "message" => "L'adresse e-mail est déjà utilisée."]);
        exit;
    }

    // Vérifier si le rôle sélectionné existe
    $checkRole = $cnx->prepare("SELECT COUNT(*) FROM roles WHERE id_role = ?");
    $checkRole->execute([$roleId]);
    if ($checkRole->fetchColumn() == 0) {
        echo json_encode(["status" => "error", "message" => "Rôle invalide."]);
        exit;
    }

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertion de l'utilisateur
    $ins = $cnx->prepare("INSERT INTO users (user_firstname, user_lastname, user_email, user_password, id_role) VALUES (?, ?, ?, ?, ?)");
    $ins->execute([$firstname, $lastname, $email, $hashedPassword, $roleId]);

    // ✅ Retourner un message de succès en JSON
    echo json_encode(["status" => "success", "message" => "Utilisateur ajouté avec succès !"]);
    exit;
} else {
    echo json_encode(["status" => "error", "message" => "Aucune donnée reçue."]);
    exit;
}
?>