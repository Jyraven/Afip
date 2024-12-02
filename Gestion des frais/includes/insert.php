<?php
if ($_POST) {
    // Validation de l'email côté
    $email = $_POST['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Erreur : Adresse e-mail invalide.");
    }
    // Vérifie si l'email existe déjà
    $checkEmail = $cnx->prepare("SELECT COUNT(*) FROM users WHERE user_email = ?");
    $checkEmail->execute([$email]);
    if ($checkEmail->fetchColumn() > 0) {
        die("Erreur : L'adresse e-mail est déjà utilisée.");
    }
    // Récupération du rôle choisi dans le formulaire
    $roleId = $_POST['role'];

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Ajout de l'utilisateur avec son rôle
    $ins = $cnx->prepare("INSERT INTO users (user_firstname, user_lastname, user_email, user_password, id_role) VALUES (?, ?, ?, ?, ?)");
    $ins->execute([$_POST['firstname'], $_POST['lastname'], $_POST['email'], $hashedPassword, $roleId]);

    echo '<div class="alert alert-success" role="alert">Utilisateur ajouté avec succès !</div>';
} else {
    die("Aucune donnée reçue.");
}
?>
<a href="index.php" class="btn btn-secondary">Retour au formulaire</a>