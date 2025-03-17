<?php
require_once('pdo/bdd.php');
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    // Validation du format d'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" role="alert">Erreur : Adresse e-mail invalide.</div>';
    } else {
        //Récupération de l'utilisateur avec son rôle
        $search = $cnx->prepare("
            SELECT users.*, roles.role 
            FROM users
            LEFT JOIN roles ON users.id_role = roles.id_role
            WHERE user_email = ?
        ");
        $search->execute([$email]);
        $user = $search->fetch();
        //Vérification des informations
        if ($user && password_verify($password, $user['user_password'])) {
            // Stockage des informations dans la session
            $_SESSION['user'] = [
                'firstname' => $user['user_firstname'],
                'lastname' => $user['user_lastname'],
                'id' => $user['id_user'],
                'role' => $user['role']
            ];
            //Redirection selon le rôle
            switch ($user['role']) {
                case 'Administrateur':
                    header("Location: templates/admin.php");
                    exit();
                case 'Comptable':
                    header("Location: templates/comptable.php");
                    exit();
                case 'Visiteur':
                    header("Location: templates/visiteur.php");
                    exit();
                default:
                    echo '<div class="alert alert-warning" role="alert">Erreur : Veuillez contacter un administrateur pour obtenir un rôle.</div>';
            }
        } else {
            // Erreur de connexion
            echo '<div class="alert alert-danger" role="alert">Erreur : Identifiants incorrects.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="public/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen login-page">  <!-- Ajoutez ici 'login-page' -->
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <img src="public/images/logo.webp" alt="Logo" class="w-52 mx-auto mb-6">
        <h2 class="text-2xl text-center text-blue-600 font-semibold mb-6">Connexion</h2>
        <form action="index.php?action=login" method="post">
            <div class="mb-4">
                <label for="userEmail" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                <input type="email" name="email" id="userEmail" class="mt-1 p-3 w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre email" required>
            </div>
            <div class="mb-6">
                <label for="userPassword" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" name="password" id="userPassword" class="mt-1 p-3 w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Entrez votre mot de passe" required>
                <a href="forgot_password.php" class="text-muted small text-blue-500 hover:text-blue-700 mt-2 inline-block">Mot de passe oublié ?</a>
            </div>
            <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Se connecter</button>
        </form>
    </div>
</body>
</html>