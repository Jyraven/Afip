<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: login.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visiteur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container visiteur-container">
        <h1>Espace Visiteur</h1>
        <p>Bonjour <?= $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']; ?>, vous êtes connecté en tant que <strong>Visiteur</strong>.</p>
    </div>
    <form action="../includes/logout.php" method="post">
            <button type="submit">Se déconnecter</button>
    </form>
</body>
</html>