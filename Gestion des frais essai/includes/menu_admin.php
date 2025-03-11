<?php
// Récupère le nom du fichier actuel (ex: "gestion_fiche.php")
$currentPage = basename($_SERVER['PHP_SELF']);

// Détecter la profondeur du dossier pour ajuster les chemins
$basePath = (in_array($currentPage, ['admin.php'])) ? "../" : "../../";
?>

<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="<?= $basePath ?>public/images/logo.webp" alt="Logo" class="w-32">
    </div>
    <div class="flex-grow flex justify-center space-x-8">
        <?php if ($currentPage !== 'admin.php'): ?>
            <a href="<?= $basePath ?>/vues/admin.php" class="text-white hover:text-gray-300 <?= $currentPage === 'admin.php' ? 'font-bold underline' : '' ?>">
                Accueil
            </a>
        <?php endif; ?>

        <?php if ($currentPage !== 'gestion_usr.php'): ?>
            <a href="<?= $basePath ?>views/users/gestion_usr.php" class="text-white hover:text-gray-300 <?= $currentPage === 'gestion_usr.php' ? 'font-bold underline' : '' ?>">
                Gestion des utilisateurs
            </a>
        <?php endif; ?>

        <?php if ($currentPage !== 'gestion_fiche.php'): ?>
            <a href="<?= $basePath ?>views/fiches/gestion_fiche.php" class="text-white hover:text-gray-300 <?= $currentPage === 'gestion_fiche.php' ? 'font-bold underline' : '' ?>">
                Gestion des fiches
            </a>
        <?php endif; ?>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
        <img src="<?= $basePath ?>public/images/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
    </div>
</div>