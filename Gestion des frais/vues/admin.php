<?php
session_start();
require_once('../includes/bdd.php');
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

    <!-- Bandeau supérieur -->
    <div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
        <!-- Logo à gauche -->
        <div>
            <img src="assets/logo.webp" alt="Logo" class="w-32">
        </div>

        <div class="flex-grow flex justify-center space-x-8">
            <a href="gestion_utilisateurs.php" class="text-white hover:text-gray-300">Gestion des utilisateurs</a>
            <a href="gestion_fiche.php" class="text-white hover:text-gray-300">Gestion des fiches</a>
        </div>

        <div class="flex items-center space-x-4">
            <span class="text-white"><?= $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']; ?></span>
            <img src="assets/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
        </div>
    </div>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">Bienvenue Administrateur</h1>
        <p class="text-center text-lg text-gray-700">Bonjour <?= $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']; ?>, vous êtes <strong>Administrateur</strong>.</p>

        <form action="../includes/logout.php" method="post" class="flex justify-center mt-8">
            <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 transition">Se déconnecter</button>
        </form>
    </div>

</body>
</html>