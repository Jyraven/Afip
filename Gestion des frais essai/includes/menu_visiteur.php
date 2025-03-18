<?php

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: login.php');
    exit();
}

// Déterminer la page actuelle pour ajuster dynamiquement les chemins
$current_page = basename($_SERVER['PHP_SELF']);

// Correction dynamique du path du logo
$logo_path = (strpos($_SERVER['PHP_SELF'], 'views/fiches') !== false) ? "../../public/images/logo.webp" : "../public/images/logo.webp";
?>

<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="<?= $logo_path ?>" alt="Logo" class="w-32">
    </div>

    <div class="flex-grow flex justify-center space-x-8">
        <?php if ($current_page === "gestion_fiche.php"): ?>
            <a href="../../templates/visiteur.php" class="text-white hover:text-gray-300">Accueil</a>
        <?php else: ?>
            <a href="../views/fiches/gestion_fiche.php" class="text-white hover:text-gray-300">Mes fiches de frais</a>
        <?php endif; ?>

        <a href="../views/fiches/gestion_remboursement.php" class="text-white hover:text-gray-300">Historique des remboursements</a>
    </div>

    <div class="flex items-center space-x-4">
        <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
        <img src="../public/images/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
    </div>
</div>
