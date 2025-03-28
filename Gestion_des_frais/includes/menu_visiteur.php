<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: ../../login.php');
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$fromFiches = strpos($_SERVER['PHP_SELF'], 'views/fiches') !== false;
$basePath = $fromFiches ? "../../" : "../";
?>

<div class="bg-gsb-blue text-white py-[3px] px-4 flex justify-between items-center shadow-md font-ui">
    <!-- Logo -->
    <div>
        <a href="<?= $basePath ?>templates/visiteur.php">
            <img src="<?= $basePath ?>public/images/logo.png" alt="Logo GSB" class="w-32 max-h-30 object-contain">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-grow flex justify-center space-x-10 text-base">
        <?php if ($currentPage === "gestion_fiche.php" || $currentPage === "gestion_remboursement.php"): ?>
            <a href="<?= $basePath ?>templates/visiteur.php"
               class="hover:text-gsb-light transition <?= $currentPage === 'visiteur.php' ? 'font-bold underline' : '' ?>">
                Accueil
            </a>
        <?php endif; ?>

        <?php if ($currentPage === "visiteur.php" || $currentPage === "gestion_remboursement.php"): ?>
            <a href="<?= $basePath ?>views/fiches/gestion_fiche.php"
               class="hover:text-gsb-light transition <?= $currentPage === 'gestion_fiche.php' ? 'font-bold underline' : '' ?>">
                Mes fiches de frais
            </a>
        <?php endif; ?>

        <?php if ($currentPage === "visiteur.php" || $currentPage === "gestion_fiche.php"): ?>
            <a href="<?= $basePath ?>views/fiches/gestion_remboursement.php?onglet=historique"
               class="hover:text-gsb-light transition <?= $currentPage === 'gestion_remboursement.php' ? 'font-bold underline' : '' ?>">
                Historique remboursement
            </a>
        <?php endif; ?>
    </nav>

    <!-- Profil + Déconnexion -->
    <div class="flex items-center space-x-4">
        <span class="text-white text-sm font-semibold font-body">
            <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>
        </span>

        <!-- Avatar -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor" class="w-10 h-10 text-white rounded-full border-2 border-white bg-gsb-blue p-1">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0"/>
        </svg>

        <!-- Déconnexion -->
        <form action="<?= $basePath ?>Auth/logout.php" method="post">
            <button type="submit" title="Se déconnecter"
                    class="text-white hover:text-red-300 transition p-2 rounded-full border border-white">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </button>
        </form>
    </div>
</div>