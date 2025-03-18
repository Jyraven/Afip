<?php
// Récupère le nom du fichier actuel (ex: "gestion_fiche.php")
$currentPage = basename($_SERVER['PHP_SELF']);

// Détecter la profondeur du dossier pour ajuster les chemins
$basePath = (in_array($currentPage, ['comptable.php'])) ? "../" : "../../";
?>

<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="<?= $basePath ?>public/images/logo.webp" alt="Logo" class="w-32">
    </div>
    <div class="flex-grow flex justify-center space-x-8">
        <?php if ($currentPage !== 'comptable.php'): ?>
            <a href="<?= $basePath ?>templates/comptable.php" class="text-white hover:text-gray-300 <?= $currentPage === 'comptable.php' ? 'font-bold underline' : '' ?>">
                Accueil
            </a>
        <?php endif; ?>

        <?php if ($currentPage !== 'gestion_remboursement.php'): ?>
            <a href="<?= $basePath ?>views/fiches/gestion_remboursement.php" class="text-white hover:text-gray-300 <?= $currentPage === 'gestion_remboursement.php' ? 'font-bold underline' : '' ?>">
                Gestion des remboursements
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