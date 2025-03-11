<?php
session_start();
require_once('../pdo/bdd.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Administrateur') {
    header('Location: login.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include('../includes/menu_admin.php'); ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">
            Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Administrateur</strong>.
        </h1>

        <form action="../Auth/logout.php" method="post" class="flex justify-center mt-8">
            <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 transition">
                Se déconnecter
            </button>
        </form>
    </div>

</body>
</html>