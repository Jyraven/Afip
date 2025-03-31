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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion | GSB</title>

  <!-- Polices Google -->
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Rajdhani:wght@600&family=Rubik:wght@500&display=swap" rel="stylesheet">

  <!-- CSS Custom -->
  <link href="/Github/Afip/Gestion_des_frais/public/css/style.css" rel="stylesheet">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="page-login font-body">
  <div class="login-wrapper w-full max-w-md p-8 rounded-xl shadow-lg">
    <h2 class="text-3xl text-center text-blue-700 font-title mb-6">Connexion</h2>

    <form action="../Gestion_des_frais/index.php?action=login" method="post">
      <div class="mb-4 text-left">
        <label for="userEmail" class="form-label text-gray-800">Adresse e-mail</label>
        <input type="email" name="email" id="userEmail" class="form-input" placeholder="Entrez votre email" required />
      </div>

      <div class="mb-6 text-left">
        <label for="userPassword" class="form-label text-gray-800">Mot de passe</label>
        <input type="password" name="password" id="userPassword" class="form-input" placeholder="Entrez votre mot de passe" required />
        <!--<a href="../forgot_password.php" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Mot de passe oublié ?</a>-->
      </div>

      <button type="submit" class="btn-primary w-full">Se connecter</button>
    </form>
  </div>
</body>
</html>